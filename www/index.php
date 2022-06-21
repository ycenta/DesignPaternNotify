<?php 

    require('Connection.php');
    require('Cryptocurrency.php');
    require('User.php');
    require('QueryBuilder.php');



    //QueryBuilder & functions
    $queryBuilder = new MysqlBuilder();

    function getValueCrypto(QueryBuilder $queryBuilder,$cryptoname) {
        $sql = $queryBuilder
            ->select('crypto_currency_result', ['*'])
            ->where('crypto_currency.base', $cryptoname)
            ->rightJoin('crypto_currency','crypto_currency_result.crypto_currency_id','crypto_currency.id')
            ->limit(0, 1)
            ->getQuery();
        return $sql;
    }

    function saveValueCrypto(QueryBuilder $queryBuilder, $cryptoname){
        $sql = $queryBuilder
            ->insert('crypto_currency_result',['crypto_currency_id','currency','amount'])
            ->getQuery();
        return $sql;
    }

    function updateValueCrypto(QueryBuilder $queryBuilder, $cryptoBase, $customJoin = ''){
        $sql = $queryBuilder
            ->update('crypto_currency_result',['amount'],$customJoin)
            ->where('crypto_currency.base',$cryptoBase)
            ->getQuery();
            return $sql;
    }

    function getSubscribedUsers(QueryBuilder $queryBuilder, $cryptoname){
        $sql = $queryBuilder
        ->select('user', ['user.*'])
        ->where('crypto_currency.base', $cryptoname)
        ->rightJoin('crypto_currency_user','crypto_currency_user.user_id','user.id')
        ->rightJoin('crypto_currency','crypto_currency_user.crypto_currency_id','crypto_currency.id')
        ->getQuery();
    return $sql;
    }

    //Script 

        //Requete API
    $json = file_get_contents('https://api.coinbase.com/v2/prices/spot?currency=EUR');
    $obj = json_decode($json);
    $currentCryptoValue = $obj->data->amount;
    $currencyCryptoValue = $obj->data->currency;




        //Requete last saved value of Crypto BTC
    $pdoConnection = new Connection();
    $lastCryptoValue = $pdoConnection->pdo
        ->query( getValueCrypto(new MysqlBuilder(),'"BTC"') )
        ->fetch();
    

    if(empty($lastCryptoValue['amount'])){
        echo "Pas de valeur dans la base donc on enregistre en base";
        $crypto_currency_id =1;

       $pdoConnection->pdo->prepare(saveValueCrypto(new MysqlBuilder(), 'BTC'))
       ->execute([$crypto_currency_id, $currencyCryptoValue, $currentCryptoValue]);
    }else{
        $crypto_currency_id = 1;
        echo "la valeur du BTC est déja présente en base, on update <br>";
        echo " valeur : " . $currentCryptoValue ."<br>";
    
        $pdoConnection->pdo->prepare(updateValueCrypto(new MysqlBuilder(), '"BTC"','RIGHT JOIN crypto_currency ON crypto_currency.id = crypto_currency_id '))
       ->execute([$currentCryptoValue]);


        echo "Valeur actuelle : $currentCryptoValue"." et valeur présidente : ".$lastCryptoValue['amount'];
        echo "<br>";
    
                //on calcule la difference
            $diff = $currentCryptoValue/$lastCryptoValue['amount'];


            //On récupère es user en base abonné au BTC
            $subscribedUsers = $pdoConnection->pdo
            ->query( getSubscribedUsers(new MysqlBuilder(),'"BTC"') );

            //On set l'objet crypto avec BTC
            $crypto = new Cryptocurrency('1','BTC','Bitcoin');


            //On subscribe en php tous les utilisateurs abonnés en base
            foreach($subscribedUsers as $subscriber){
                $user = new User($subscriber['id'],$subscriber['firstname'],$subscriber['lastname'],$subscriber['email']);
                $crypto->subscribe($user);
            }


            echo "ratio : $diff <br>";
            if($diff > 1.01){ 
                echo "augmentation du bitcoin on alert les users abonnés";
                //Notify si la valeur est + haute
                $crypto->notify(); 

            }elseif($diff < 0.99){
                echo "diminution du prix du bitcoin";
            }
     

    }



  




    //exo partie 1

    // $user1 = New user(1,'email@email.fr','test','jean');
    // $user2 = New user(2,'email2@email.com','françois','fredreic');
    // $user3 = New user(3,'email3@email.com','jean','jean');

    // $crypto->subscribe($user1);
    // $crypto->subscribe($user2);
    // $crypto->subscribe($user3);



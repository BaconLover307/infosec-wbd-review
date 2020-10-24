<?php
    ['connect_db' => $connect_db ] = require __DIR__ . '/db_connect.php';

    $validate_token = function($token){
        try{
            $pdo = $connect_db();
            $sql = "SELECT * FROM SESSION WHERE hash_id = :hash_id";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':hash_id', $token);

            $stmt->execute();
            if($stmt->rowCount() === 1){
                $session = $stmt->fetch(PDO::FETCH_OBJ);
                
                if(strtotime($session->expire_time) <= 
                    strtotime(date("Y-m-d H:i:s"))){
                    return array(
                        'is_valid' => false,
                        'message' => 'token expired'
                    );
                }

                return array(
                    'is_valid' => true,
                    'message' => 'token valid',
                    'username' => $session->username,
                    'is_superuser' => $session->is_superuser
                );
            } else {
                return array(
                    'is_valid' => false,
                    'message' => 'token invalid',
                );
            }
        } catch (Exception $error){
            // $error_message = $error->getMessage();
            throw $error;
        }
    };

    $make_token = function($username, $password){
        require(__DIR__ . "/db_connect.php");
        $curr_time = date("Y-m-d H:i:s");
        $expire_time = date('Y-m-d H:i:s', 
                strtotime('+4 hour +20 minutes',strtotime($curr_time)));

        $username = trim($username);
        $password = trim($password);
        
        $sql = "SELECT * FROM users WHERE username = :username";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":username", $username);

        try{
            $stmt->execute();
            if($stmt->rowCount() === 1){
                $user = $stmt->fetch(PDO::FETCH_OBJ);
                // echo $password_hash;
                if(password_verify($password, $user->password_hash)){
                    $hash_id = password_hash($username . $curr_time);

                    $sql = "INSERT INTO sessions (hash_id, username, is_superuser, login_time, expire_time),
                                VALUES (:hash_id, :username, :is_superuser, :login_time, :expire_time)";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(":hash_id", $hash_id);
                    $stmt->bindParam(":username", $username);
                    $stmt->bindParam(":is_superuser", $user->is_superuser);
                    $stmt->bindParam(":login_time", $curr_time);
                    $stmt->bindParam(":expire_time", $expire_time);

                    $stmt->execute();

                    return array(
                        'is_success' => true,
                        'message' => 'login success',
                        'session_id' => $hash_id,
                        'username' => $username,
                        'is_superuser' => $user->is_superuser,
                        'login_time' => $login_time,
                        'expire_time'=> $expire_time
                    );

                } 
                else {
                    return array(
                            'is_success' => false,
                            'message' => 'password or username wrong'
                        );
                }
            }
            else {
                return array(
                    'is_success' => false,
                    'message' => 'password or username wrong'
                );
            }
            
        } catch (Exception $error){
            throw $error;
        }
        
    };

    $destroy_token = function($hash_id){

    };

    return compact('validate_token', 'make_token', 'destroy_token');
?>
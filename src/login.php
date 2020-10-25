<?php
    ['validate_token' => $validate_token ] = require 'utils/authentication.php';
    ['make_token' => $make_token ] = require 'utils/authentication.php';
    ['connect_db' => $connect_db ] = require 'utils/db_connect.php';

    $error_message = "";

    $session = array();
    if(isset($_COOKIE["sessionID"])){
        try{
            $session = $validate_token($_COOKIE["sessionID"]);
            if($session['is_valid']){
                header("location: dashboard.php");
                exit;
            }
            $error_message = $session['message'];
        }
        catch(Exception $error){
            $error_message = $error->getMessage();
        }
    }


    if($_SERVER["REQUEST_METHOD"] === "POST"){
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);
        try{
            $token = $make_token($email, $password);
            if($token['is_success']){
                // echo $token['session_id'];
                setcookie('sessionID', $token['session_id'], [
                    'expires' => time() + 3600 * 5,
                    'path' => '/',
                    'domain' => 'localhost',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'None',
                ]);
                header("location: dashboard.php");
                exit;
            } else {
                $error_message = $token['message'];
            }
        } 
        catch (Exception $error){
            $error_message = $error->getMessage();
        }
    }
    
?>

<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
    <link rel="stylesheet" href="../public/css/register.css" type="text/css">
</head>

<body>
    <div class="spacer"></div>
    <header>
        Willy Wangky Choco Factory
    </header>
    <div class="spacer"></div>
    <div class="errorMessage">
        <?php echo $error_message ?>
    </div>
    <main>
        <form id="login" action="login.php" method="POST">
            <table>
                <tr>
                    <th>Email</th>
                </tr>
                <tr>
                    <td>
                        <input id="emailField" name="email" type=email>
                    </td>
                </tr>
                <tr id="emailErrorRow" style="display: none">
                    <td>
                        <p id="emailError"> </p>
                    </td>
                </tr>
                <tr>
                    <th>Password</th>
                </tr>
                <tr>
                    <td>
                        <input type="password" id="passwordField" name="password">
                    </td>
                </tr>
                <tr>
                    <th>
                        <button type="submit" form="login">
                            Login
                        </button>
                    </th>
                </tr>
            </table>
        </form>
    </main>
    <div class="spacer"></div>
</body>

</html>
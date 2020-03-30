<?php
   header("access-control-allow-origin:*");
   header('Content-type:application/json;charset=utf-8');
   header('Access-Control-Allow-Methods: POST');

    $_POST = json_decode(file_get_contents('php://input'), true); 

    if(isset($_POST["req"])) {
        
        $req = htmlspecialchars(strip_tags($_POST["req"])); 
        switch($req) {

            case 'register': 
                createUser();
                break;
            case 'login':
                login();
                break; 
            default;
                echo "Ei ole!"; 
                http_response_code(404); 
        }

    } else {
        echo "Virheee! :(((((( ";
        http_response_code(400);
    }

    function connect() {
        $yhteys = new mysqli("localhost", "root", "", "prj_db") or die("Connection fail ".mysqli_connect_error());
        $yhteys->set_charset("utf8");
        return $yhteys;    
    }

    function createUser() {

        $_POST = json_decode(file_get_contents('php://input'), true); 
        $resMsg = "";

        if(strlen($_POST["username"])>0 && strlen($_POST["password"])>0) {
            $username=htmlspecialchars(strip_tags($_POST["username"])); 
            $password=htmlspecialchars(strip_tags($_POST["password"])); 
            $email=htmlspecialchars(strip_tags($_POST["email"])); 
            $resMsg = "{\"message\":\"Tiedot ok: ".$username.", ".$password.", ".$email."\"}";
        } else {
            echo "{\"message\":\"käyttis tai salasana puuttuu\"}"; 
            exit(); 
        }
        
        $yhteys = connect(); 
        $checkDb = $yhteys->query("SELECT * FROM users WHERE username ='".$username."'"); 
        
        if(mysqli_num_rows($checkDb)>0) {
            
            echo "{\"message\":\"Käyttäjätunnus ".$username." on jo käytössä. Valitse toinen\"}"; 
            mysqli_close($yhteys);
            http_response_code(400);
            exit(); 
        
        } else {
            $password_h = password_hash($password, PASSWORD_BCRYPT);
            $q = "INSERT INTO users (username, password, email) VALUES ('$username', '$password_h', '$email')"; 
            if($yhteys->query($q)) {
               $resMsg = "{\"message\":\"Käyttäjätunnus luotu\"}"; 
            } else {
                echo "{\"message\":\"Wooops :(( -- ".$mysqli->error."\"}"; 
                mysqli_close($yhteys);
                http_response_code(400);
                exit(); 
            }
            echo $resMsg; 
            mysqli_close($yhteys);

        }
    }
    function login() {

        $_POST = json_decode(file_get_contents('php://input'), true); 

        if(strlen($_POST["username"])>0 && strlen($_POST["password"])>0) {
            $username=htmlspecialchars(strip_tags($_POST["username"])); 
            $password=htmlspecialchars(strip_tags($_POST["password"])); 
        } else {
            http_response_code(401); 
            echo "{\"message\":\"käyttis tai salasana puuttuu\"}"; 
            exit(); 
        }
        $yhteys = connect(); 
        $emailCheck =  $yhteys->query("SELECT username FROM users WHERE username = '$username' LIMIT 0,1"); 

        if(mysqli_num_rows($emailCheck)==0) {
            
            echo "{\"message\":\"Käyttäjätunnusta ei löydy\"}";  
            http_response_code(401);
            mysqli_close($yhteys);
            exit(); 
        
        } else {

            $q = $yhteys->query("SELECT * FROM users WHERE username = '$username' LIMIT 0,1"); 
            $row = mysqli_fetch_row($q);
            $passhash = $row[2];   

            if(!password_verify($password, $passhash)) {

                echo "{\"message\":\"Väärä salasana!\"}";
                http_response_code(401);
                mysqli_close($yhteys);
                exit(); 

            } else {
                // TODO: TOKEN !! 
                echo "{\"user\": {
                    \"userId\":\"".$row[0]."\",
                    \"username\":\"".$row[1]."\",
                    \"email:\":\"".$row[3]."\"  
                }, \"token\":\"eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1bmlxdWVfbmFtZSI6IjM1IiwibmJmIjoxNTg0NjIyNDUzLCJleHAiOjE1ODUyMjcyNTMsImlhdCI6MTU4NDYyMjQ1M30.afk3WBhSgJxJ5ZF-2IATbkvl9SY7Jl0UeJScbaxtqvw\"}";

                mysqli_close($yhteys);

            }
    
        }

    }




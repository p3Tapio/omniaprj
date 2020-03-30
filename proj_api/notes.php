<?php 
    header("access-control-allow-origin:*");
    header('Content-type:application/json;charset=utf-8');
    header('Access-Control-Allow-Methods: POST');

    $_POST = json_decode(file_get_contents('php://input'), true); 
    if(isset($_POST["req"])) {

        $req = htmlspecialchars(strip_tags($_POST["req"])); 

        switch($req) {

            case 'getnotes':
                getNotes();
                break;
            case 'addnote':
                addNote(); 
                break; 
            case 'delete': 
                delNote();
                break; 
            case 'edit':
                editNote();
                break; 
            default;
                echo "{\"message\":\"EI OO\"}"; 
                http_response_code(404); 
        }

    }
    function getNotes() {

        if(isset($_POST["userid"])) {
            
            $userid = (int)$_POST["userid"]; 
            
            $yhteys = connect(); 
            $notes = $yhteys->query("SELECT * FROM notes WHERE userid = '$userid' ORDER BY luotu desc"); 
            
            if(mysqli_num_rows($notes)>0) {
                
                $resMsg = '[';
                for($i = 0; $i< mysqli_num_rows($notes); $i++) {
                    if($i>0) {
                        $resMsg.=','.json_encode(mysqli_fetch_object($notes));
                    } else {
                        $resMsg.=''.json_encode(mysqli_fetch_object($notes));; 
                    }
                }
                $resMsg.=']';

            } else {
                http_response_code(404);
                $resMsg = "{\"message\":\"EI tietoi\"}"; 
            }

        } else {
            http_response_code(404);
            $resMsg = "{\"message\":\"Ei käyttistä\"}"; 

        }
        echo $resMsg;
        mysqli_close($yhteys);
 
    }
    function addNote() {
        $resMsg = ""; 
        if(strlen($_POST["userid"])>0 && strlen($_POST["otsikko"])>0 && strlen($_POST["otsikko"])>0) {
            $userid = (int)$_POST["userid"]; 
            $otsikko = htmlspecialchars(strip_tags($_POST["otsikko"])); 
            $teksti = htmlspecialchars(strip_tags($_POST["teksti"])); 
        } else {
            echo "{\"message\":\"tietoja puuttuu\"}"; 
            http_response_code(400);
            exit(); 
        }

        $yhteys = connect(); 
        $q = "INSERT INTO notes (userid,otsikko,teksti) VALUES ('$userid', '$otsikko', '$teksti')";
        if($yhteys->query($q)) {
            $resMsg = "{\"message\":\"tiedot välitetty kantaan\"}"; 
        } else {
            $resMsg = "Jotain häikkää:\n".$yhteys->error."\n";  
            http_response_code(400);
        }
        echo $resMsg;
        mysqli_close($yhteys);

    }
    function delNote() {

        if(strlen($_POST["noteid"])>0) {
            
            $noteid = (int)$_POST["noteid"];
            $yhteys = connect(); 

            $noteCheck = $yhteys->query("SELECT noteid FROM notes WHERE noteid ='$noteid'"); 
            if(mysqli_num_rows($noteCheck)==0) { 
                $resMsg = "{\"message\":\"muistiipanoa ei löydy\"}"; 
                http_response_code(404);
            } else {
                
                $q = "DELETE FROM notes WHERE noteid = '$noteid'";

                if($yhteys->query($q)) {
                    $resMsg = "{\"message\":\"muistiipano poistettu\"}"; 
                } else {
                    $resMsg = "Jotain häikkää:\n".$yhteys->error."\n";  
                    http_response_code(400);
                }
            }   
        }  else {
            $resMsg = "{\"message\":\"Wooops :(( -- ".$mysqli->error."\"}"; 
            http_response_code(400);

        }
        echo $resMsg; 
        mysqli_close($yhteys);
    }
    function editNote() {

        if(strlen($_POST["noteid"])>0) {
            
            $noteid = (int)$_POST["noteid"];
            $otsikko = htmlspecialchars(strip_tags($_POST["otsikko"])); 
            $teksti = htmlspecialchars(strip_tags($_POST["teksti"])); 

            $yhteys = connect(); 
            $noteCheck = $yhteys->query("SELECT noteid FROM notes WHERE noteid ='$noteid'");
            
            if(mysqli_num_rows($noteCheck)==0) { 

                $resMsg = "{\"message\":\"muistiipanoa ei löydy\"}"; 
                http_response_code(404);

            } else {

                $q = "UPDATE notes SET otsikko = '$otsikko', teksti = '$teksti' WHERE noteid = '$noteid'";
              
                if($yhteys->query($q)) {
                    $resMsg = "{\"message\":\"muistiipano muokattu\"}"; 
                } else {
                    $resMsg = "Jotain häikkää:\n".$yhteys->error."\n";  
                    http_response_code(400);
                }
            }
        
        } else {
            $resMsg = "{\"message\":\"Wooops :(( -- ".$mysqli->error."\"}"; 
            http_response_code(400);
        }


        echo $resMsg; 
        mysqli_close($yhteys);
    }
    function connect() {
         $yhteys = new mysqli("127.0.0.1:51034", "azure", "6#vWHD_$", "prj_db") or die("yhteyden muodostus epäonnistui");
        $yhteys->set_charset("utf8");
        return $yhteys;    
    }
?>

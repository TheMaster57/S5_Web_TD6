<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"> 

<html xmlns="http://www.w3.org/1999/xhtml">   

    <head>    
        <title>Vérification</title>   
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />  
        <link rel="stylesheet" type="text/css" href="css/style.css" />
    </head> 

    <body> 

        <?php
        include 'base.php';

        /* header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
          header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date dans le passé */

        /* Test du nom d'utilisateur */
        $correct = true;
        if (strlen($_POST['nomUtilisateur']) < 4) {
            echo("<p>Erreur : Le nom d'utilisateur est incorrect (<4 caractères)</p>");
            $correct = false;
        } else if (!checkUserName($_POST['nomUtilisateur'])) {
            echo "<p>Erreur: Le nom d'utilisateur existe déjà dans la base.";
            $correct = false;
        }

        /* Test du mot de passe */
        if (strlen($_POST['password']) < 8) {
            echo("<p>Erreur : Le mot de passe est incorrect (<8 caractères)</p>");
            $correct = false;
        }
        /* Test de la confirmation du mot de passe */
        if ($_POST['password'] != $_POST['confirmpassword']) {
            echo("<p>Erreur : La confirmation est différentes du mot de passe</p>");
            $correct = false;
        }
        /* Test de l'adresse email */
        if ($_POST['email'] == "" || strpos($_POST['email'], '@') == false || strpos($_POST['email'], '.') == false) {
            echo("<p>Erreur : L'email est incorrect</p>");
            $correct = false;
        }
        /* Test des genres sélectionnés */
        $compteur = 0;
        foreach ($_POST as $key => $val) {
            // Si y'a le mot check dans la clé, on incrémente le compteur
            if (substr($key, 0, 5) == "check") {
                $compteur = $compteur + 1;
            }
        }
        // Si on en 0 ou 1, alors c'est pas correct
        // echo "Nombre de genre sélectionnés : " . $compteur;
        if ($compteur < 2) {
            echo("<p>Erreur : Il faut au moins sélectionner 2 genres.</p>");
            $correct = false;
        }

        if ($correct == false) {
            redirection();
        } else {
            /* Si on arrive ici, on va procéder à l'enregistrement */
            echo "<p>Les champs ont été validés par le serveur</p>";

            /* Cryptage du mot de passe */
            $newPassword = md5($_POST['password']);

            /* Insertion de l'utlisateur */
            $id = addUser($_POST['nomUtilisateur'], $_POST['email'], $newPassword);

            /* Insertion des genres */
            addUserGenres($id);
        }



        /* Fonction qui redirige vers le formulaire (temps d'attente = 3s) */

        function redirection() {
            echo "<p id=\"redirection\">Redirection vers le formulaire dans 3 secondes ...</p>";
            header('Refresh: 3; URL=formulaire.php');
        }

        /* Fonction qui vérifie si un utilisateur existe déjà dans la base */

        function checkUserName($username) {
            $c = Base::getConnection();

            $query = $c->prepare("SELECT * FROM users WHERE name='" . $username . "'");
            $query->execute();

            $d = $query->fetch();
            if ($d == null) {
                return true;
            } else {
                return false;
            }
        }

        /* Fonction qui ajoute un utilisateur dans la base
         * Exemple: INSERT INTO users (name, email, password) VALUES ('jimi', 'jimi@club27.com', '2e3817293') */

        function addUser($name, $email, $password) {
            $c = Base::getConnection();

            $query = $c->prepare("INSERT INTO users(name, email, password) VALUES ('" . $name . "', '" . $email . "', '" . $password . "')");
            $query->execute();
            
            return $c->lastInsertId();
        }

        
        function addUserGenres($id) {
            $c = Base::getConnection();

            foreach ($_POST as $key => $val) {
                if (substr($key, 0, 5) == "check") {
                    $query = $c->prepare("INSERT INTO users_genres(user_id, genre) VALUES ('" . $id . "', '" . $val . "')");
                    $query->execute();
                }
            }
        }
        ?>




    </body>
</html>
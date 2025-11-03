<?php

function verify_login($db, $username, $password){
    try{
        $query = 'SELECT password
                    FROM users
                    WHERE username = :username';
        $statement = $db->prepare($query);
        $statement->bindValue(':username', $username);  // Cambié $username por $user_username
        $statement->execute();
        $row = $statement->fetch();
        $statement->closeCursor();

        if ($row) {
            $hash = $row['password'];
            return password_verify($password, $hash);
        } else {
            return false;  // Retorna false si no se encuentra el usuario
        }

    } catch(PDOException $e) {
        error_log("Database error in verify_login: " . $e->getMessage());
        throw $e;
    } catch (Exception $e) {
        error_log("Error in verify_login: " . $e->getMessage());
        throw $e;
    }
}



function fetch_users($db, $username){
    try{
        // Datos del usuario
        $query = 'SELECT * FROM users WHERE username = :username';
        $stmt = $db->prepare($query);
        $stmt->bindValue(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch();
        $stmt->closeCursor();
        return $user;

    } catch(PDOException $e) {
        error_log("Database error in fetch_users: " . $e->getMessage());
        throw $e;
    }
}



?>
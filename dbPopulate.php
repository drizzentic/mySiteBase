<?php

	$dbh = new PDO('sqlite:users.db');
	$dbh->exec("INSERT INTO Users (Name, Pass, Mail, Active) VALUES ('Test', 'test', 'coucou@test.com', 0)");

?>
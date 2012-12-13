<?php
	// web/index.php

	require_once __DIR__.'/vendor/autoload.php';

	$dbh;
	try {
		$dbh = new PDO('sqlite:users.db');
		$dbh->exec("CREATE TABLE IF NOT EXISTS Users (Id INTEGER PRIMARY KEY, Name TEXT, Pass TEXT, Mail TEXT, Active INTEGER)");
	}
	catch (Exception $e) {
		echo 'Caught exception: ',  $e->getMessage(), "\n";
	}

	$app = new Silex\Application();

	$app->register(new Silex\Provider\TwigServiceProvider(), array(
		'twig.path' => __DIR__.'/views',
	));

	$app->match('/', function() use ($app) {
		return $app['twig']->render('index.html', array(
				'prefix' => './',
				'title' => 'Index',
			));
	})->method('GET');

	$app->get('/login', function() use ($app) {
		return $app['twig']->render('login.html', array(
				'prefix' => './',
				'title' => 'Login',
			));
	});

	$app->get('/register', function() use ($app) {
		return $app['twig']->render('register.html', array(
				'prefix' => './',
				'title' => 'Register',
			));
	});

	$app->get('/users', function() use ($app, $dbh) {
		$result = $dbh->query('SELECT * FROM Users');
		return $app['twig']->render('users.html', array(
				'prefix' => './',
				'users' => $result,
				'title' => 'Users',
			));
	});

	$app->get('/hello/{name}', function ($name) use ($app) {
		return $app['twig']->render('hello.html', array(
				'prefix' => '../',
				'title' => 'Hello',
				'name' => $name,
		));
	});

	$app->post('/register', function() use ($app, $dbh) {
		$name = $_POST['Name'];
		$pass = $_POST['Password'];
		$mail = $_POST['Mail'];
		$stmt = $dbh->prepare('INSERT INTO Users (Name, Pass, Mail, Active) VALUES (:name, :pass, :mail, :active)');
		$stmt->bindParam(':name', $name, PDO::PARAM_STR);
		$stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
		$stmt->bindParam(':mail', $mail, PDO::PARAM_STR);
		$stmt->bindParam(':active', $active, PDO::PARAM_INT);
		$stmt->execute();
		return $app['twig']->render('registerPost.html', array(
				'title' => 'Registration',
				'prefix' => './',
				'name' => $name,
				'pass' => $pass,
				'mail' => $mail,
		));
	});

	$app['debug'] = true;

	$app->run();
?>
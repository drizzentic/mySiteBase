<?php
	// web/index.php
	session_start();

	require_once __DIR__.'/vendor/autoload.php';

	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\Response;

	$dbh;
	try {
		$dbh = new PDO('sqlite:users.db');
		$dbh->exec("CREATE TABLE IF NOT EXISTS Users (Id INTEGER PRIMARY KEY, Name TEXT, Pass TEXT, Mail TEXT, Active INTEGER)");
	}
	catch (Exception $e) {
		echo 'Caught exception: ',  $e->getMessage(), "\n";
	}

	$app = new Silex\Application();

	/** TODO : Doctrine
	$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
		'db.options' => array(
			'driver'   => 'pdo_sqlite',
			'path'     => __DIR__.'/users.db',
		),
	));
	*/

	/**
	*	Middlewares
	*/

	$checkLogin = function(Request $request) use ($app) {
		if (!isset($_SESSION['user'])) {
			return $app->redirect('/Novus/login');
		}
	};

	/**
	*	Controllers
	*/
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

	$app->get('/logout', function() use ($app) {
		if (isset($_SESSION['user'])) {
			unset($_SESSION['user']);
			session_destroy();
		}
		return $app['twig']->render('login.html', array(
				'prefix' => './',
				'title' => 'Logout',
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

	$app->get('/users/{id}', function($id) use ($app, $dbh) {
		$stmt = $dbh->prepare('SELECT * FROM Users WHERE Id = :id');
		$stmt->bindParam(':id', $id, PDO::PARAM_STR);
		$stmt->execute();
		$res = $stmt->fetchAll();
		return $app['twig']->render('profile.html', array(
				'prefix' => '../',
				'user' => $res[0],
				'title' => 'User Profile',
			));
	});

	$app->get('/hello/{name}', function ($name) use ($app) {
		if (isset($_SESSION['user'])) {
			$name = $_SESSION['user'];
		}
		return $app['twig']->render('hello.html', array(
				'prefix' => '../',
				'title' => 'Hello',
				'name' => $name,
		));
	})->before($checkLogin);

	$app->post('/register', function(Request $request) use ($app, $dbh) {
		$name = $request->get('Name');
		$pass = $request->get('Password');
		$mail = $request->get('Mail');
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

	$app->post('/login', function(Request $request) use ($app, $dbh) {
		$id = $request->get('Identifier');
		$pass = $request->get('Password');
		$stmt = $dbh->prepare('SELECT * FROM Users WHERE (Name = :id OR Mail = :id) AND Pass = :pass');
		$stmt->bindParam(':id', $id, PDO::PARAM_STR);
		$stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
		$stmt->execute();
		$res = $stmt->fetchAll();
		if (count($res) > 0) {
			$_SESSION['user'] = $res[0][1];
		}
		return $app['twig']->render('loginPost.html', array(
				'title' => 'Registration',
				'prefix' => './',
				'result' => $res,
		));
	});

	$app['debug'] = true;

	$app->run();
?>
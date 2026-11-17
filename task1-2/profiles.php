
<?php

$xml = simplexml_load_file("LV2.xml");

if ($xml === false) {
    die("XML file could not be loaded.");
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>User Profiles</title>

    <style>
        body {
            font-family: Arial;
            background: #f2f2f2;
            margin: 30px;
        }

        h1 {
            text-align: center;
        }

        .container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .profile {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
        }

        .email {
            color: blue;
        }
    </style>
</head>

<body>

<h1>User Profiles</h1>

<div class="container">

<?php foreach ($xml->record as $person): ?>

    <div class="profile">

        <img src="<?php echo htmlspecialchars($person->slika); ?>">

        <h2>
            <?php echo htmlspecialchars($person->ime . " " . $person->prezime); ?>
        </h2>

        <p class="email">
            <?php echo htmlspecialchars($person->email); ?>
        </p>

        <p>
            Gender: <?php echo htmlspecialchars($person->spol); ?>
        </p>

        <p>
            <?php echo htmlspecialchars($person->zivotopis); ?>
        </p>

    </div>

<?php endforeach; ?>

</div>

</body>
</html>

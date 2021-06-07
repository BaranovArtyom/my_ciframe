<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="hw35_1.php" method="post">
    <h1>Анкета</h1></br>
    <label for="fio">FIO</label><input type="text" name="fio"></br><br>
    <label for="age">Age</label><input type="text" name="age"></br><br>
    
    <label for="education">Education</label>
    <select name="education[]" multiple size="2">
        <option value="magistr">Magistr</option>
        <option value="bakalavr">Bakalavr</option>
        <option value="middle">Middler</option>
    </select>
    <br>
    
    <h4>SEX</h4>
    <input type="radio" name="sex" value="man">MAN
    <input type="radio" name="sex" value="woman">WOMAN<br><br>
    
    <label for="lang">Language</label>
    <select name="lang[]" multiple size="2">
        <option value="php">PHP</option>
        <option value="js">JS</option>
        <option value="html">HTML</option>
    </select><br><br>

    <label for="quastion">How much 2+2</label><input type="text" name="quastion"></br><br>

    <input type="submit" value="send">
    </form>
    
</body>
</html>
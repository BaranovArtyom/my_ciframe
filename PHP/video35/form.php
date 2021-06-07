
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="form2.php" method="post">
        <input type="text" name="name"><br>
        <input type="text" name="age"><br>
        
        <input type="hidden" name="hid" value="test">

        <input type="checkbox" name="php">PHP<br>
        <input type="checkbox" name="js">JS<br>
        <input type="checkbox" name="python">Python<br>
        
        <input type="checkbox" name="color" value="red">Red<br>
        <input type="checkbox" name="color" value="blue">Blue<br>
        <input type="checkbox" name="color" value="white">White<br>
        
        <select name="period[]" multiple size="3" id="">
            <option name="mouth" value="1">Месяц</option>
            <option name="polgoda" value="6">Полгода</option>
            <option name="year" value="12">Год</option>
            <option name="never" value="0">Не помню</option>
        </select>
        <br>
        <input type="radio" name="slide" value="1">1<br>
        <input type="radio" name="slide" value="2">2<br>
        <input type="radio" name="slide" value="3">3<br>

        <input type="submit" value="send">
    </form>
    
</body>
</html>
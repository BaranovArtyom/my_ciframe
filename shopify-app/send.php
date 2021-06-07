<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="index.php" method="post">
		<input size = 50px type="text" name="url" value="" />
        
        <select name="format" required>
            <option value="csv">CSV</option>
            <option value="xlsx">XLSV</option>
        </select>
        
        <input type="submit" value="скачать">
    </form>
 </body>
</html>


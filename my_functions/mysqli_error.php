if (mysqli_connect_errno())
  {
    echo "Не удалось подключиться к MySQL" ;
	mysqli_connect_error(); 
  }
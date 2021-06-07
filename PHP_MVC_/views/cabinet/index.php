<?php include ROOT.'/views/layout/header.php';?>

<section>
    <div class="container">
        <div class="row">

            <h1>Кабинет пользователя</h1>
            <h3>Привет, <?= $user['name'];?></h3>

            <ul>
                <li><a href="/PHP_MVC_/cabinet/edit">Редактирование данных</a></li>
                <li><a href="/PHP_MVC_/user/history">Список покупок</a></li>
            </ul>
        </div>
    </div>
</section>

<?php include ROOT.'/views/layout/footer.php';?>
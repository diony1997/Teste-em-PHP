<?php
/*  session_start();

  if (empty($_SESSION['ra']) and  empty($_SESSION['senha'])) {
  header('Location: login.php');
  }
 */
require_once 'banco.php';
$banco = new Banco();
?>

<html>
    <head>
        <meta charset="utf-8">
        <title>Pesquisa CPA</title>
    </head>
    <body>
        <form action="paginaRelatorio_resp.php" method="post">
            <?php
            $banco->impressao_bloco();
            ?>

        </form>
    </body>
</html>

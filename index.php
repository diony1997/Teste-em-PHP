<html>
    <head>
        <meta charset="UTF-8">
        <title>Teste PHP</title>
    </head>
    <body>
        <form action="index_resp.php" method="post">
            codigo: <input name="codigo"/> <br>
            Nome: <input name="nome"/> <br>
            Preco: <input name="preco"/> <br>
            <input type="submit"/> <br>

        </form>


        URL: <input type="text" id="novaURL"/> <br>
        <input type="submit" value="Gerar QRCode" onclick="gerarQR()"/><br>
        <div id="qrcode"></div>


        <script src = "qrcode.min.js"></script>

        <script>
            var url = document.getElementById('novaURL');
            var qrcode = new QRCode(document.getElementById('qrcode'));

            function gerarQR() {
                qrcode.makeCode(url.value);
            }
        </script>
        <?php
        require_once 'banco.php';
        $banco = new Banco();
        session_start();
        if (!(isset($_SESSION['ra']) == true) and ( !isset($_SESSION['senha']) == true)) {
            header('location: login.php');
        }
        /*
          $banco->apagarPergunta(3);
         * 
         */
        /*
          Exemplo inserir pergunta
          $banco->inserirPergunta(5, "Pergunta teste", "mat3");
         * 
         */
        /* Exemplo buscar pergunta
         * $banco->buscarPergunta("mmm");
          session_start();
          if (isset($_SESSION['message'])) {
          print $_SESSION['message'];
          $_SESSION['message'] = null;
          }
         */
        ?>

    </body>
</html>

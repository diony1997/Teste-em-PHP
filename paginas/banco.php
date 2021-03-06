<?php

require_once './Conexao.php';

class Banco {

    private $linkDB;

    function __construct() {
        $this->linkDB = new Conexao("bankunovo");
    }

    function login($ra, $senha) {
        $sql = "SELECT * FROM `aluno` WHERE RA = '" . $ra . "' && senha = '" . $senha . "'";
        $stmt = mysqli_prepare($this->linkDB->con, $sql);
        if (!$stmt) {
            die("Falha no comando SQL: COnferir RA e Senha");
        }
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows != 0) {
            $_SESSION['ra'] = $ra;
            $_SESSION['senha'] = $senha;
            $this->buscarPerguntas();
            $this->buscarBloco($ra);
            header('location: paginaPerguntas2.php');
        } else {
            unset($_SESSION['ra']);
            unset($_SESSION['senha']);
            $_SESSION['loginerror'] = 2;
            header('Location: login.php');
        }
    }

    function checarRA($ra) {
        $sql = "SELECT * FROM `aluno` WHERE ra = '" . $ra . "'";
        $stmt = mysqli_prepare($this->linkDB->con, $sql);
        if (!$stmt) {
            die("Falha no comando SQL: Checar RA");
        }
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows != 0) {
            return true;
        } else {
            return false;
        }
    }

    function checarUser($user) {
        $sql = "SELECT * FROM `user` WHERE id = '" . $user . "'";
        $stmt = mysqli_prepare($this->linkDB->con, $sql);
        if (!$stmt) {
            die("Falha no comando SQL: Checar User");
        }
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows != 0) {
            return true;
        } else {
            return false;
        }
    }

    function loginUser($user, $senha) {
        $sql = "SELECT * FROM `user` WHERE id = '" . $user . "' && senha = '" . $senha . "'";
        $stmt = mysqli_prepare($this->linkDB->con, $sql);
        if (!$stmt) {
            die("Falha no comando SQL: COnferir user e Senha");
        }
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows != 0) {
            $_SESSION['user'] = $user;
            $_SESSION['senha'] = $senha;
            header('location: paginaAdmin.php');
        } else {
            unset($_SESSION['user']);
            unset($_SESSION['senha']);
            $_SESSION['loginerror'] = 2;
            header('Location: loginUser.php');
        }
    }

    function buscarBloco() {
        $sql = "SELECT bloco FROM `aluno` WHERE ra = '" . $_SESSION['ra'] . "'";
        $stmt = mysqli_prepare($this->linkDB->con, $sql);
        if (!$stmt) {
            die("Falha no comando SQL: Buscar Aluno.Bloco");
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $_SESSION['bloco'] = $row["bloco"];
    }

    function inserirPergunta($curso, $conteudo, $disciplina, $professor, $tipo, $dataInicial, $dataFinal) {
        if ($tipo == 3) {
            $sql = "INSERT INTO `pergunta` (`id`, `conteudo`, `tipo`,`idDisciplina`, `idProfessor`, `idCurso`, `dataInicial`, `dataFinal`)"
                    . " VALUES (NULL, '" . $conteudo . "', '" . $tipo . "', NULL, NULL, '" . $curso . "', '" . $dataInicial . "', '" . $dataFinal . "')";
        } else if ($tipo == 2) {
            $sql = "INSERT INTO `pergunta` (`id`, `conteudo`, `tipo`,`idDisciplina`, `idProfessor`, `idCurso`, `dataInicial`, `dataFinal`)"
                    . " VALUES (NULL, '" . $conteudo . "', '" . $tipo . "', NULL, '" . $professor . "', '" . $curso . "', '" . $dataInicial . "', '" . $dataFinal . "')";
        } else {
            $sql = "INSERT INTO `pergunta` (`id`, `conteudo`, `tipo`,`idDisciplina`, `idProfessor`, `idCurso`, `dataInicial`, `dataFinal`)"
                    . " VALUES (NULL, '" . $conteudo . "', '" . $tipo . "', '" . $disciplina . "', '" . $professor . "', '" . $curso . "', '" . $dataInicial . "', '" . $dataFinal . "')";
        }
        $stmt = mysqli_prepare($this->linkDB->con, $sql);
        if (!$stmt) {
            die("Falha no comando SQL: Inserção de Pergunta");
        }
        $stmt->execute();
    }

    //busca todas perguntas disponiveis no curso
    function buscarPerguntas() {
        $data_atual = date('Y-m-d');
        //buscar o curso pelo ra
        $sql = "SELECT idcurso FROM `aluno` WHERE ra = '" . $_SESSION['ra'] . "'";
        $stmt = mysqli_prepare($this->linkDB->con, $sql);
        if (!$stmt) {
            die("Falha no comando SQL: buscarPerguntas() buscar Curso");
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $curso = $row["idcurso"];

        //busca as perguntas do curso
        $sql = "SELECT id FROM `pergunta` WHERE idCurso = '" . $curso . "' and dataInicial <= '" . $data_atual . "' and dataFinal >= '" . $data_atual . "'";
        $stmt = mysqli_prepare($this->linkDB->con, $sql);
        if (!$stmt) {
            die("Falha no comando SQL: busca das perguntas " . $data_atual);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $saida = "";
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $saida .= $row["id"] . ";";
            }
        } else {
            header('location:fim.php');
        }
        $pergunta = explode(";", $saida);
        unset($pergunta[sizeof($pergunta) - 1]);
        $pergunta = array_values($pergunta);
        $this->conferirJafeito($pergunta);
    }

    //confere se ja se o aluno ja respondeu as perguntas
    function conferirJafeito($pergunta) {
        for ($x = 0; $x < sizeof($pergunta); $x++) {
            $sql = "SELECT * FROM `aluno_resp` WHERE alunora = '" . $_SESSION['ra'] . "' and idPergunta = '" . $pergunta[$x] . "'";
            $stmt = mysqli_prepare($this->linkDB->con, $sql);
            if (!$stmt) {
                die("Falha no comando SQL: Buscar Aluno_Resp");
            }
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                unset($pergunta[$x]);
                $pergunta = array_values($pergunta);
                $x--;
            }
        }
        $_SESSION['pergunta'] = $pergunta;
        $_SESSION['checar'] = TRUE;
    }

    //retorna uma string com todas perguntas encontradas
    function exibirPergunta() {
        $pergunta = $_SESSION['pergunta'];
        //para checar se buscou duas perguntas
        $pergunta2 = $pergunta3 = $pergunta4 = $pergunta5 = FALSE;
        $saida = "";
        if (empty($pergunta)) {
            header('location:fim.php');
        } else {
            $saida .= $this->pergunta($pergunta[0]);
            //confere se existe uma pergunta adicional no array
            if (sizeof($pergunta) > 1) {
                $pergunta2 = TRUE;
                $saida .= $this->pergunta($pergunta[1]);
            }
            if (sizeof($pergunta) > 2) {
                $pergunta3 = TRUE;
                $saida .= $this->pergunta($pergunta[2]);
            }
            if (sizeof($pergunta) > 3) {
                $pergunta4 = TRUE;
                $saida .= $this->pergunta($pergunta[3]);
            }
            if (sizeof($pergunta) > 4) {
                $pergunta5 = TRUE;
                $saida .= $this->pergunta($pergunta[4]);
            }

            if ($_SESSION['checar'] == FALSE) {
                $_SESSION['checar'] = TRUE; //variavel para checar o formulario foi enviado ou não
                //Retirar do vetor as perguntas exibidas
                unset($pergunta[0]);
                if ($pergunta2) {
                    unset($pergunta[1]);
                }
                if ($pergunta3) {
                    unset($pergunta[2]);
                }
                if ($pergunta4) {
                    unset($pergunta[3]);
                }
                if ($pergunta5) {
                    unset($pergunta[4]);
                }
                $pergunta = array_values($pergunta);
                $_SESSION['pergunta'] = $pergunta;
            }
            return $saida;
        }
    }

    //retorna uma String: idPergunta, Conteudo
    function pergunta($idpergunta) {
        $saida = "";
        $sql = "SELECT id,conteudo,tipo FROM `pergunta` WHERE id = '" . $idpergunta . "'";
        $stmt = mysqli_prepare($this->linkDB->con, $sql);
        if (!$stmt) {
            die("Falha no comando SQL: select pergunta " . $idpergunta);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $saida .= $row["id"] . "#" . $row["conteudo"] . "#" . $row["tipo"] . ";";
        }
        return $saida;
    }

    function inserirResposta($valor, $idpergunta, $tipo) {
        $this->checarTabelaResp($idpergunta);
        //tipo != 0, aluno selecionou q a resposta não é direcionada a ele,(não cursa a disciplina ou não tem aula com o professor)
        if ($tipo == 0) {
            $sql = "UPDATE `resposta` SET `valor` = (valor + " . $valor . "), `cont` = (cont + 1) WHERE `resposta`.`blocoturma` = '" . $_SESSION['bloco'] . "' AND `resposta`.`idPergunta` = '" . $idpergunta . "'";
        } else {
            $sql = "UPDATE `resposta` SET `valor` = (valor + " . $valor . "), `cont` = (cont + 1), `nulos` = (nulos + 1) WHERE `resposta`.`blocoturma` = '" . $_SESSION['bloco'] . "' AND `resposta`.`idPergunta` = '" . $idpergunta . "'";
        }
        $stmt = mysqli_prepare($this->linkDB->con, $sql);
        if (!$stmt) {
            die("Falha no comando SQL: Update resposta");
        }
        $stmt->execute();
    }

    //gerar uma relação entre o ra e a disciplina para sinalizar que o aluno ja respondeu
    function gerarRelacao($idpergunta) {
        $sql = "INSERT INTO `aluno_resp` (`alunora`, `idPergunta`) VALUES ('" . $_SESSION['ra'] . "', '" . $idpergunta . "')";
        $stmt = mysqli_prepare($this->linkDB->con, $sql);
        if (!$stmt) {
            die("Falha no comando SQL: gerar Relação aluno_resp");
        }
        $stmt->execute();
    }

    function gerarRelatorio($bloco) {
        $sql = "SELECT curso.nome as Curso, pergunta.conteudo as Pergunta, replace(resposta.valor/(resposta.cont-resposta.nulos),'.',',') as Media , resposta.cont as QTD_Respostas, resposta.nulos as QTD_Nulos, resposta.blocoturma as Bloco from resposta\n"
                . "inner JOIN pergunta on pergunta.id = resposta.idPergunta\n"
                . "INNER JOIN curso on curso.id = pergunta.idCurso\n"
                . "where blocoturma = '" . $bloco . "'";
        $stmt = mysqli_prepare($this->linkDB->con, $sql);
        if (!$stmt) {
            die("Falha no comando SQL: Gerar relatorio");
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $tabela = $this->criarTabela();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
//                $saida .= $row["Curso"] . '#' . $row["Pergunta"] . "#" . $row["Media"] . "#" . $row["QTD_Respostas"] . ";";
                $tabela = $this->inserirTabela($row, $tabela);
            }
        } else {
            echo 'Bloco invalido';
        }
        $tabela = $this->fecharTabela($tabela);
        return $tabela;
    }

    //função para conferir se existe a tabela de resposta
    function checarTabelaResp($idpergunta) {
        $sql = "SELECT * FROM `resposta` WHERE blocoturma = '" . $_SESSION['bloco'] . "' && idPergunta = '" . $idpergunta . "'";
        $stmt = mysqli_prepare($this->linkDB->con, $sql);
        if (!$stmt) {
            die("Falha no comando SQL: checar tabela resposta");
        }
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows < 1) {
            $sql = "INSERT INTO `resposta` (`blocoturma`, `idPergunta`) VALUES ('" . $_SESSION['bloco'] . "','" . $idpergunta . "')";
            $stmt = mysqli_prepare($this->linkDB->con, $sql);
            if (!$stmt) {
                die("Falha no comando SQL: inserir tabela resposta");
            }
            $stmt->execute();
        }
    }

    function criarTabela() {
        $tabela = '<table id="Relatorio" border="1" width="100%" style="background-color: white;" >'; //abre table
        $tabela .= '<thead>'; //abre cabeçalho
        $tabela .= '<tr>'; //abre uma linha
        $tabela .= '<th>Curso</th>'; // colunas do cabeçalho
        $tabela .= '<th>Pergunta</th>';
        $tabela .= '<th>Média</th>';
        $tabela .= '<th>Quantidade de Respostas</th>';
        $tabela .= '<th>Quantidade de Nulos</th>';
        $tabela .= '<th>Bloco</th>';
        $tabela .= '</tr>'; //fecha linha
        $tabela .= '</thead>'; //fecha cabeçalho
        $tabela .= '<tbody>'; //abre corpo da tabela

        return $tabela;
    }

    function inserirTabela($row, $tabela) {
        $tabela .= '<tr>'; // abre uma linha
        $tabela .= '<td>' . $row["Curso"] . '</td>';
        $tabela .= '<td>' . $row["Pergunta"] . '</td>';
        $tabela .= '<td>' . $row["Media"] . '</td>';
        $tabela .= '<td>' . $row["QTD_Respostas"] . '</td>';
        $tabela .= '<td>' . $row["QTD_Nulos"] . '</td>';
        $tabela .= '<td>' . $row["Bloco"] . '</td>';
        $tabela .= '</tr>'; // fecha linha

        return $tabela;
    }

    function fecharTabela($tabela) {
        $tabela .= '</tbody>'; //fecha corpo
        $tabela .= '</table>'; //fecha tabela

        return $tabela;
    }

    function impressao_curso() {
        $sql = "SELECT id, nome FROM `curso`";
        $stmt = mysqli_prepare($this->linkDB->con, $sql);
        if (!$stmt) {
            die("Falha no comando SQL: Impressao Curso");
        }
        $stmt->execute();
        $result = $stmt->get_result();
        echo '<p>Curso</p>';
        if ($result->num_rows > 0) {
            echo '<select name="curso" id="opCurso" onchange="atualizarDisc(this.value)">';
            while ($row = $result->fetch_assoc()) {
                echo '<option value="' . $row["id"] . '">' . $row["nome"] . '</option>';
            }
            echo '</select>';
        } else {
            echo '<h4>Nenhum Curso encontrado</h4>';
        }
    }

    function impressao_professor($tipo, $curso) {
        //tipo 1 procura pelo curso
        if ($tipo == 2) {
            $sql = "SELECT professor.id as profId, professor.nome as profNome, disciplina.nome as discNome FROM `professor`\n"
                    . "                inner join Disciplina on disciplina.idProfessor = professor.id\n"
                    . "                inner join Curso on curso.id = disciplina.idCurso\n"
                    . "                where curso.id = '" . $curso . "'";
            $stmt = mysqli_prepare($this->linkDB->con, $sql);
            if (!$stmt) {
                die("Falha no comando SQL: Impressao Professor tipo 2");
            }
            $stmt->execute();
            $result = $stmt->get_result();
            echo '<p>Professor</p>';
            if ($result->num_rows > 0) {
                echo '<select name="professor">';
                while ($row = $result->fetch_assoc()) {
                    echo '<option value="' . $row["profId"] . '">' . $row["profNome"] . ' - ' . $row["discNome"] . '</option>';
                }
                echo '</select>';
            } else {
                echo '<h4 id="noProf">Nenhum Professor encontrado</h4>';
            }
        } else {
            //procura pela disciplina
            $sql = "SELECT professor.id as profId, professor.nome as profNome FROM `professor`\n"
                    . "                inner join Disciplina on disciplina.idProfessor = professor.id\n"
                    . "                where disciplina.id = " . $curso;
            $stmt = mysqli_prepare($this->linkDB->con, $sql);
            if (!$stmt) {
                die("Falha no comando SQL: Impressao Professor tipo 1");
            }
            $stmt->execute();
            $result = $stmt->get_result();
            echo '<p>Professor</p>';
            if ($result->num_rows > 0) {
                echo '<select name="professor">';
                while ($row = $result->fetch_assoc()) {
                    echo '<option value="' . $row["profId"] . '">' . $row["profNome"] . '</option>';
                }
                echo '</select>';
            } else {
                echo '<h4>Nenhum Professor encontrado</h4>';
            }
        }
    }

    function impressao_disciplina($curso) {
        $sql = "SELECT disciplina.id as discId, disciplina.nome as discNome FROM `disciplina`"
                . "inner join Curso on disciplina.idCurso = Curso.id where curso.id ='" . $curso . "'";
        $stmt = mysqli_prepare($this->linkDB->con, $sql);
        if (!$stmt) {
            die("Falha no comando SQL: Impressao Disciplina");
        }
        $stmt->execute();
        $result = $stmt->get_result();
        echo '<p>Disciplina</p>';
        if ($result->num_rows > 0) {
            echo '<select name="disciplina" id="disc2" onchange="atualizarProf(this.value)">';
            while ($row = $result->fetch_assoc()) {
                echo '<option value="' . $row["discId"] . '">' . $row["discNome"] . '</option>';
            }
            echo '</select>';
        } else {
            echo '<h4 id="helper">Nenhuma Disciplina encontrada</h4>';
        }
    }

    function impressao_bloco() {
        $sql = "SELECT blocoturma FROM `resposta` group by blocoturma";
        $stmt = mysqli_prepare($this->linkDB->con, $sql);
        if (!$stmt) {
            die("Falha no comando SQL: checar bloco resposta");
        }
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            echo '<select name="bloco" id="opBloco" onchange="atualizar()">';
            while ($row = $result->fetch_assoc()) {
                echo '<option value="' . $row["blocoturma"] . '">' . $row["blocoturma"] . '</option>';
            }
            echo '</select>';
        } else {
            echo '<h2> Nenhuma Pergunta Respondida </h2>';
        }
    }

    function __destruct() {
        $this->linkDB = NULL;
    }

}

<?php
session_start();
session_unset(); 
session_destroy(); 

// A MÁGICA ESTÁ AQUI:
// Em vez de só dar header, vamos usar um script para limpar o navegador
echo "<script>
    sessionStorage.clear(); // Limpa tudo o que o logar.php salvou
    window.location.href = '../login.html'; // Volta pro login
</script>";
exit;
?>s
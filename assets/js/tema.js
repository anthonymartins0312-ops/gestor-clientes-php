/* ==========================================================
   Gestor de Clientes — tema claro/escuro
   Carregado no <head> para aplicar o tema antes de a página
   aparecer. O padrão é o escuro; o botão de tema troca e o
   navegador lembra a escolha.
   ========================================================== */

(function () {
    var tema = null;

    try {
        tema = localStorage.getItem('gestor-clientes-tema');
    } catch (erro) {
        // Navegador sem armazenamento: usa o tema padrão
    }

    if (tema !== 'claro' && tema !== 'escuro') {
        tema = 'escuro';
    }

    document.documentElement.setAttribute('data-tema', tema);
})();

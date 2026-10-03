/* ==========================================================
   Gestor de Clientes — interações da interface
   ========================================================== */

document.addEventListener('DOMContentLoaded', () => {
    iniciarTema();
    iniciarSidebar();
    iniciarAlertas();
    iniciarMascaraTelefone();
    iniciarContadorCaracteres();
    iniciarMostrarSenha();
    iniciarFiltroAutomatico();
    iniciarConfirmacaoExclusao();
});

/**
 * Alterna entre tema claro e escuro e guarda a escolha no navegador.
 * O tema inicial (escuro por padrão) já foi aplicado por assets/js/tema.js.
 */
function iniciarTema() {
    const botoes = document.querySelectorAll('[data-theme-toggle]');
    const raiz = document.documentElement;

    const atualizarBotoes = () => {
        const escuro = raiz.getAttribute('data-tema') === 'escuro';
        const rotulo = escuro ? 'Ativar tema claro' : 'Ativar tema escuro';
        botoes.forEach((botao) => {
            botao.setAttribute('aria-label', rotulo);
            botao.setAttribute('title', rotulo);
        });
    };

    botoes.forEach((botao) => {
        botao.addEventListener('click', () => {
            const novoTema = raiz.getAttribute('data-tema') === 'escuro' ? 'claro' : 'escuro';
            raiz.setAttribute('data-tema', novoTema);
            try {
                localStorage.setItem('gestor-clientes-tema', novoTema);
            } catch (erro) {
                // Sem armazenamento, o tema vale só até recarregar a página
            }
            atualizarBotoes();
        });
    });

    atualizarBotoes();
}

/**
 * Abre/fecha o menu lateral em telas pequenas.
 */
function iniciarSidebar() {
    const sidebar = document.getElementById('sidebar');
    const botao = document.getElementById('menuToggle');
    const overlay = document.getElementById('sidebarOverlay');

    if (!sidebar || !botao || !overlay) return;

    const abrir = () => {
        sidebar.classList.add('open');
        overlay.classList.add('show');
        botao.setAttribute('aria-expanded', 'true');
    };

    const fechar = () => {
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
        botao.setAttribute('aria-expanded', 'false');
    };

    botao.addEventListener('click', () => {
        sidebar.classList.contains('open') ? fechar() : abrir();
    });
    overlay.addEventListener('click', fechar);
    document.addEventListener('keydown', (evento) => {
        if (evento.key === 'Escape') fechar();
    });
}

/**
 * Mensagens flash: botão de fechar + fechamento automático após 5s.
 */
function iniciarAlertas() {
    document.querySelectorAll('[data-alert]').forEach((alerta) => {
        const fechar = () => {
            alerta.classList.add('hide');
            setTimeout(() => alerta.remove(), 300);
        };

        const botao = alerta.querySelector('.alert-close');
        if (botao) botao.addEventListener('click', fechar);

        setTimeout(fechar, 5000);
    });
}

/**
 * Máscara de telefone: (11) 98765-4321 ou (11) 3456-7890.
 */
function mascararTelefone(valor) {
    const d = valor.replace(/\D/g, '').slice(0, 11);

    if (d.length === 0) return '';
    if (d.length <= 2) return `(${d}`;
    if (d.length <= 6) return `(${d.slice(0, 2)}) ${d.slice(2)}`;
    if (d.length <= 10) return `(${d.slice(0, 2)}) ${d.slice(2, 6)}-${d.slice(6)}`;
    return `(${d.slice(0, 2)}) ${d.slice(2, 7)}-${d.slice(7)}`;
}

function iniciarMascaraTelefone() {
    document.querySelectorAll('input[data-mask="telefone"]').forEach((campo) => {
        campo.value = mascararTelefone(campo.value);
        campo.addEventListener('input', () => {
            campo.value = mascararTelefone(campo.value);
        });
    });
}

/**
 * Contador de caracteres para textareas com data-contador.
 */
function iniciarContadorCaracteres() {
    document.querySelectorAll('textarea[data-contador]').forEach((campo) => {
        const contador = document.getElementById(campo.dataset.contador);
        if (!contador) return;

        const atualizar = () => {
            contador.textContent = `${campo.value.length}/${campo.maxLength}`;
        };

        campo.addEventListener('input', atualizar);
        atualizar();
    });
}

/**
 * Botão para mostrar/ocultar a senha.
 */
function iniciarMostrarSenha() {
    document.querySelectorAll('[data-toggle-password]').forEach((botao) => {
        const campo = document.getElementById(botao.dataset.togglePassword);
        if (!campo) return;

        botao.addEventListener('click', () => {
            const mostrar = campo.type === 'password';
            campo.type = mostrar ? 'text' : 'password';
            botao.classList.toggle('active', mostrar);
            botao.setAttribute('aria-label', mostrar ? 'Ocultar senha' : 'Mostrar senha');
        });
    });
}

/**
 * Envia o formulário de filtros automaticamente ao trocar o status.
 */
function iniciarFiltroAutomatico() {
    document.querySelectorAll('[data-auto-submit]').forEach((campo) => {
        campo.addEventListener('change', () => {
            if (campo.form) campo.form.submit();
        });
    });
}

/**
 * Modal de confirmação antes de excluir um cliente.
 */
function iniciarConfirmacaoExclusao() {
    const formularios = document.querySelectorAll('form[data-confirm]');
    if (!formularios.length) return;

    const modal = document.getElementById('confirmModal');
    let formularioPendente = null;

    const abrirModal = (form) => {
        formularioPendente = form;
        modal.querySelector('[data-modal-nome]').textContent = form.dataset.nome || 'este cliente';
        modal.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');
        modal.querySelector('.btn[data-modal-close]').focus();
    };

    const fecharModal = () => {
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
        formularioPendente = null;
    };

    formularios.forEach((form) => {
        form.addEventListener('submit', (evento) => {
            // Sem o modal na página, usa a confirmação nativa do navegador
            if (!modal) {
                const nome = form.dataset.nome || 'este cliente';
                if (!window.confirm(`Deseja realmente excluir ${nome}?`)) {
                    evento.preventDefault();
                }
                return;
            }

            evento.preventDefault();
            abrirModal(form);
        });
    });

    if (!modal) return;

    modal.querySelectorAll('[data-modal-close]').forEach((elemento) => {
        elemento.addEventListener('click', fecharModal);
    });

    modal.querySelector('[data-modal-confirm]').addEventListener('click', () => {
        if (!formularioPendente) return;
        const form = formularioPendente;
        fecharModal();
        form.submit(); // submit() não dispara o evento novamente
    });

    document.addEventListener('keydown', (evento) => {
        if (evento.key === 'Escape' && modal.classList.contains('show')) {
            fecharModal();
        }
    });
}

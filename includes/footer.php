        </main>

        <footer class="footer">
            <span>&copy; <?= date('Y') ?> Gestor de Clientes</span>
            <span>Projeto demonstrativo para portfólio</span>
        </footer>
    </div>
</div>

<!-- Modal de confirmação de exclusão -->
<div class="modal" id="confirmModal" aria-hidden="true">
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="confirmModalTitulo">
        <div class="modal-icon"><?= icone('trash') ?></div>
        <h2 id="confirmModalTitulo">Excluir cliente?</h2>
        <p>Você está prestes a excluir <strong data-modal-nome></strong>. Esta ação não pode ser desfeita.</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" data-modal-close>Cancelar</button>
            <button type="button" class="btn btn-danger" data-modal-confirm>Sim, excluir</button>
        </div>
    </div>
</div>

<script src="assets/js/script.js"></script>
</body>
</html>

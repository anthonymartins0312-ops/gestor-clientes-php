<?php
/**
 * Formulário de cliente (reutilizado em cliente_novo.php e cliente_editar.php).
 *
 * Variáveis esperadas:
 * @var array  $dados      Valores dos campos
 * @var array  $erros      Erros de validação (campo => mensagem)
 * @var string $textoBotao Texto do botão de envio
 */
?>
<form method="post" class="form">
    <?= csrf_campo() ?>

    <div class="form-grid">
        <div class="form-group">
            <label for="nome" class="form-label">Nome completo <span class="required">*</span></label>
            <input type="text" id="nome" name="nome" class="form-control<?= classe_erro($erros, 'nome') ?>"
                   value="<?= e($dados['nome']) ?>" maxlength="150" required
                   placeholder="Ex.: Maria Oliveira">
            <?= mensagem_erro($erros, 'nome') ?>
        </div>

        <div class="form-group">
            <label for="email" class="form-label">E-mail <span class="required">*</span></label>
            <input type="email" id="email" name="email" class="form-control<?= classe_erro($erros, 'email') ?>"
                   value="<?= e($dados['email']) ?>" maxlength="150" required
                   placeholder="cliente@exemplo.com">
            <?= mensagem_erro($erros, 'email') ?>
        </div>

        <div class="form-group">
            <label for="telefone" class="form-label">Telefone</label>
            <input type="tel" id="telefone" name="telefone" class="form-control<?= classe_erro($erros, 'telefone') ?>"
                   value="<?= e($dados['telefone']) ?>" maxlength="15" inputmode="numeric"
                   data-mask="telefone" placeholder="(00) 00000-0000">
            <?= mensagem_erro($erros, 'telefone') ?>
        </div>

        <div class="form-group">
            <label for="empresa" class="form-label">Empresa</label>
            <input type="text" id="empresa" name="empresa" class="form-control<?= classe_erro($erros, 'empresa') ?>"
                   value="<?= e($dados['empresa']) ?>" maxlength="150"
                   placeholder="Nome da empresa (opcional)">
            <?= mensagem_erro($erros, 'empresa') ?>
        </div>

        <div class="form-group form-full">
            <span class="form-label">Status <span class="required">*</span></span>
            <div class="status-options">
                <?php foreach (STATUS_CLIENTE as $valor => $rotulo): ?>
                    <label class="status-option">
                        <input type="radio" name="status" value="<?= e($valor) ?>"
                            <?= $dados['status'] === $valor ? 'checked' : '' ?>>
                        <span class="status-pill status-pill-<?= e($valor) ?>"><?= e($rotulo) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <?= mensagem_erro($erros, 'status') ?>
        </div>

        <div class="form-group form-full">
            <label for="observacoes" class="form-label">Observações</label>
            <textarea id="observacoes" name="observacoes" class="form-control<?= classe_erro($erros, 'observacoes') ?>"
                      maxlength="1000" rows="5" data-contador="contadorObs"
                      placeholder="Anotações sobre o cliente, preferências, histórico de contato..."><?= e($dados['observacoes']) ?></textarea>
            <div class="form-hint">
                <span>Opcional. Máximo de 1000 caracteres.</span>
                <span id="contadorObs">0/1000</span>
            </div>
            <?= mensagem_erro($erros, 'observacoes') ?>
        </div>
    </div>

    <div class="form-actions">
        <a href="clientes.php" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary"><?= e($textoBotao) ?></button>
    </div>
</form>

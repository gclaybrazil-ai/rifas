<?php
header('Content-Type: application/json');
require_once '../config.php';

// Este script pode ser chamado por um Cron Job diariamente
// Ou manualmente pelo administrador para processar o ciclo de pagamentos.

try {
    // 1. Buscar configurações do ciclo e valor mínimo
    $stmtConf = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('minimo_saque', 'ciclo_pagamento_dias')");
    $configs = $stmtConf->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $minSaque = (float)($configs['minimo_saque'] ?? 20.00);
    $cicloDias = (int)($configs['ciclo_pagamento_dias'] ?? 15);

    // 2. Buscar afiliados que atingiram o tempo do ciclo E possuem saldo mínimo
    // DATEDIFF(hoje, data_ultimo_saque) >= ciclo
    $sql = "SELECT id, nome, pix_key, saldo 
            FROM afiliados 
            WHERE saldo >= ? 
            AND DATEDIFF(NOW(), data_ultimo_saque) >= ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$minSaque, $cicloDias]);
    $afiliadosParaPagar = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $processados = 0;
    $totalValor = 0;

    if (count($afiliadosParaPagar) > 0) {
        $pdo->beginTransaction();
        
        $stmtUpdateAf = $pdo->prepare("UPDATE afiliados SET saldo = 0, data_ultimo_saque = NOW() WHERE id = ?");
        $stmtInsertSaque = $pdo->prepare("INSERT INTO saques (afiliado_id, valor, chave_pix, status, data_solicitacao) VALUES (?, ?, ?, 'pendente', NOW())");

        foreach ($afiliadosParaPagar as $af) {
            $valor = $af['saldo'];
            
            // Registra o saque como pendente
            $stmtInsertSaque->execute([$af['id'], $valor, $af['pix_key']]);
            
            // Zera o saldo e atualiza a data do último saque
            $stmtUpdateAf->execute([$af['id']]);

            $processados++;
            $totalValor += $valor;
        }

        $pdo->commit();
    }

    echo json_encode([
        'success' => true,
        'message' => "Ciclo processado com sucesso.",
        'afiliados_pagos' => $processados,
        'valor_total_pendente' => $totalValor
    ]);

} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['error' => 'Erro ao processar pagamentos: ' . $e->getMessage()]);
}

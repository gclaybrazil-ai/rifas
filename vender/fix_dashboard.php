<?php
$f = 'dashboard.php';
$c = file_get_contents($f);
// Target the exact line 172 roughly
$c = preg_replace(
    '/<span class="text-\[9px\] font-black bg-yellow-50 text-yellow-600 px-3 py-1 rounded-full uppercase tracking-widest italic">[^<]+<\/span>/u',
    '<?php if (($r[\'status\'] ?? \'\') === \'ativa\'): ?>
                                                <span class="text-[9px] font-black bg-green-100 text-green-700 px-3 py-1 rounded-full uppercase tracking-widest italic">Ativado 🔥</span>
                                            <?php else: ?>
                                                <span class="text-[9px] font-black bg-yellow-50 text-yellow-600 px-3 py-1 rounded-full uppercase tracking-widest italic tracking-tighter">Aguardando Ativação</span>
                                            <?php endif; ?>',
    $c
);
file_put_contents($f, $c);
echo "FIXED dashboard status";

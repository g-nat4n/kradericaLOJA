<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

$stats = vesta_atelier_stats();
$cards = [
	['label' => 'Peças no acervo', 'value' => (string) $stats['products']],
	['label' => 'Disponíveis', 'value' => (string) $stats['available']],
	['label' => 'Vendidas', 'value' => (string) $stats['sold']],
	['label' => 'Pedidos', 'value' => (string) $stats['orders']],
	['label' => 'Receita confirmada', 'value' => vesta_atelier_format_brl($stats['revenue'])],
];
$links = [
	['href' => Vesta_Core_Atelier::url('produtos/novo'), 'label' => 'Nova peça'],
	['href' => Vesta_Core_Atelier::url('best-sellers'), 'label' => 'Mais vendidos'],
	['href' => Vesta_Core_Atelier::url('categorias'), 'label' => 'Categorias'],
	['href' => Vesta_Core_Atelier::url('pedidos'), 'label' => 'Pedidos'],
];
?>
<p class="eyebrow">Painel</p>
<h1 class="display" style="margin-top:0.5rem;font-size:2.4rem;">Atelier Vesta</h1>
<p class="atelier-lead">Cadastre peças, organize os mais vendidos da home, categorias e acompanhe pedidos.</p>

<div class="atelier-chips">
	<?php foreach ($links as $link) : ?>
		<a class="atelier-chip" href="<?php echo esc_url($link['href']); ?>"><?php echo esc_html($link['label']); ?></a>
	<?php endforeach; ?>
</div>

<div class="atelier-stats">
	<?php foreach ($cards as $card) : ?>
		<article class="atelier-stat">
			<p class="atelier-stat__label"><?php echo esc_html($card['label']); ?></p>
			<p class="atelier-stat__value"><?php echo esc_html($card['value']); ?></p>
		</article>
	<?php endforeach; ?>
</div>

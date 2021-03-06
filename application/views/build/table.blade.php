<ul class="pager pull-left">
{{ $pagination->previous() }}
</ul>
<ul class="pager pull-right">
{{ $pagination->next() }}
</ul>
<table class='table build-table'>
<tbody>
	<tr>
		<th></th>
		<th class="views">{{ __('build.hcsc') }}</th>
		<th>{{ __('build.level') }}</th>
		<th>{{ __('build.paragon') }}</th>
		<th>{{ __('build.name') }}</th>
		<th>{{ __('build.skills') }}</th>
		<th>{{ __('build.passives') }}</th>
		<th>{{ __('diablo.dps') }}</th>
		<th>{{ __('diablo.ehp') }}</th>
		<th></th>
	</tr>
<? foreach($builds as $build): ?>
	<tr class='build-row'>
		<td class="icon"><img src="/img/icons/<?= $build->class ?>.png"/></td>
		<td><?= ($build->hardcore) ? "<span class='neg' title='Hardcore'>HC</span>" : "<span class='pos' title='Softcore'>SC</span>" ?> </td>
		<td class="stat"><?= $build->level ?></td>
		<td class="stat"><?= $build->paragon ?></td>
		<td class='name'><?= HTML::buildLink($build) ?></td>
		<td class="skills">
			<? if($build->actives): ?>
				<? foreach($build->actives as $active): ?>
					<? 
						if($active == "undefined") {
							?>
							<img data-class="<?= $build->class ?>" src="/img/icons/unknown.png" class="skill-tip"/>
							<?
							continue;
						}
						$explode = explode("~", $active);
						$skill = $explode[0];
					?>
					<img data-class="<?= $build->class ?>" data-id="<?= $active ?>" data-class="<?= $build->class ?>" src="/img/icons/<?= $build->class ?>-<?= $skill ?>.png" class="skill-tip"/>
				<? endforeach ?>
			<? endif ?>
		</td>
		<td class="skills">
			<? if($build->passives): ?>
				<? foreach($build->passives as $passive): ?>
					<? 
						$explode = explode("~", $passive);
						$skill = $explode[0];
					?>
					<img data-class="<?= $build->class ?>" data-id="<?= $passive ?>" src="/img/icons/<?= $build->class ?>-<?= $skill ?>.png" class="skill-tip"/>
				<? endforeach ?>
			<? endif ?>
		</td>
		<td class="stat"><?= HTML::prettyStat($build->stats['dps']) ?></td>
		<td class="stat"><?= HTML::prettyStat($build->stats['ehp']) ?></td>
	</tr>
<? endforeach; ?>
</tbody>
</table>
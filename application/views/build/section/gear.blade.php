<div>
  <ul class="nav nav-tabs">
    <li class="active">
			<a href="#gear-paperdoll" data-toggle="tab">Paperdoll</a>
		</li>
    <li><a href="#gear-overview" data-toggle="tab">Overview</a></li>
    <li><a href="#gear-contributions" data-toggle="tab">DPS/EHP Contributions</a></li>
  </ul>
  <div class="tab-content">
    <div class="tab-pane" id="gear-overview">
			<div>
				<div class='tab-content'>
					@foreach($build->gear as $item) 
						@if($item && $item->id)
							{{ View::make('build.section.item')->with('item', $item) }}
						@endif
					@endforeach
				</div>
				<ul id='build-gear'>
					@foreach($build->gear as $slot => $gear) 
						<li>{{ HTML::itemLink($gear, array('toggle' => true, 'slot' => $slot)) }}</li>
					@endforeach
				</ul>
			</div>
    </div>
    <div class="tab-pane" id="gear-contributions">
			Contributions
    </div>
    <div class="tab-pane active" id="gear-paperdoll">
			<img src="http://placehold.it/500x400&text=Full Paper Doll for Character, like Battle.net">
    </div>
  </div>
</div>
<script type="text/javascript" charset="utf-8">
	$('#gear-overview a[data-toggle="tab"]').on('show', function (e) {
		$('#gear-overview .nav').hide();
		$('#gear-overview .return-home').bind('click', function() {
			$(this).closest('.item-detail').removeClass("active");
			$('#gear-overview .nav').show();
		});
		// console.log(e.target);
	})
</script>
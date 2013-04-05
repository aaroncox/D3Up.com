<style type="text/css" media="screen">
	.skills {
		padding: 0;
		margin: 0;
		list-style: none;
/*		border: 1px solid #fff;*/
	}
	.skills .skill {
/*		border: 1px solid #fff;*/
	}
	.skills .skill .skill-icon {
		float: left;
		width: 36px;
		height: 36px;
	}
	.skills .skill .skill-details h3 {
		font-size: 28px;
	}
	.skills .skill .skill-details {
		margin-left: 50px;
	}
</style>
<ul class="nav nav-pills">
  <li class='active'><a href="#skills-overview" data-toggle="tab">Overview</a></li>
</ul>
<div class="tab-content">
  <div class="tab-pane active">
		<script id="skill-overview" type="text/x-handlebars-template">
			<ul class='skills'>
				{{ HTML::hb('#each skills.actives') }}
					<li class='skill' data-skill='{{ HTML::hb('@key') }}'>
						<div class='skill-icon icon-frame'>
							<img src='/img/icons/{{ HTML::hb('../meta.heroClass') }}-{{ HTML::hb('skillName @key') }}.png'>
						</div>
						<div class='skill-details row-fluid'>
							<div class='span9'>
								<h3>Skill Name</h3>
							</div>
							<div class='span3'>
								{{ HTML::hb('dps') }}
							</div>
						</div>
					</li>
				{{ HTML::hb('/each') }}
				{{ HTML::hb('#each skills.passives') }}
				<li class='skill' data-skill='{{ HTML::hb('@key') }}'>
					<div class='skill-icon icon-frame'>
						<img src='/img/icons/{{ HTML::hb('../meta.heroClass') }}-{{ HTML::hb('skillName @key') }}.png'>
					</div>
					<div class='skill-details'>
						<h3>{{ HTML::hb('@key') }}</h3>
					</div>
				</li>
				{{ HTML::hb('/each') }}
			</ul>
		</script>
  </div>
</div>
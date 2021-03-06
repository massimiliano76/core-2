<?php
if (!isConnect()) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$date = array(
	'start' => date('Y-m-d', strtotime(config::byKey('history::defautShowPeriod') . ' ' . date('Y-m-d'))),
	'end' => date('Y-m-d'),
);
?>

<ul class="nav nav-tabs reportModeHidden" role="tablist" style="margin-top:4px;">
	<li role="presentation" class="active"><a href="#historytab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-chart-bar"></i> {{Historique}}</a></li>
	<li role="presentation"><a id="bt_tabTimeline" href="#timelinetab" aria-controls="profile" role="tab" data-toggle="tab" style="padding:10px 5px !important"><i class="far fa-clock"></i> {{Timeline}}</a></li>
	<div id="div_alertHistory" style="display: inline-block;"></div>
</ul>

<div class="tab-content" style="overflow:auto;overflow-x: hidden;">
	<div role="tabpanel" class="tab-pane active" id="historytab">
		<div class="row row-overflow" data-offset="70">
			<div class="col-lg-3 col-md-4 col-sm-5 bs-sidebar">
				<ul class="nav nav-list bs-sidenav">
					<li>
						<div class="input-group input-group-sm">
							<textarea class="form-control roundedLeft" id='in_calculHistory' placeholder="{{Historique calculé}}" ></textarea>
							<span class="input-group-btn">
								<a class="btn btn-default" id="bt_findCmdCalculHistory" title="{{Sélectionner la commande}}"><i class="fas fa-list-alt"></i>
								</a><a class="btn btn-success" id="bt_displayCalculHistory" title="{{Afficher le graphique calculé}}"><i class="fas fa-check"></i>
								</a><a class="btn btn-default roundedRight" id="bt_configureCalculHistory" title="{{Configuration des formules de calcul}}"><i class="fas fa-cogs"></i></a>
							</span>
						</div>
					</li>
				</ul>
				<ul id="ul_history" class="nav nav-list bs-sidenav">
					<li class="nav-header"><i class="icon techno-courbes3"></i> {{Historique}}
						<a id="bt_openCmdHistoryConfigure" class="btn btn-default btn-sm pull-right" style="top:-5px;padding: 5px 10px;margin-right: 3px;"><i class="fas fa-cogs"></i> {{Configuration}}</a>
					</li>
					<li class="filter"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" /></li>
					<?php
					$object_id = -1;
					$historyAll = cmd::allHistoryCmd();
					foreach ($historyAll as $cmd) {
						$eqLogic = $cmd->getEqLogic();
						if (!$eqLogic->hasRight('r')) {
							continue;
						}
						$_echo = '';
						if ($object_id != $eqLogic->getObject_id()) {
							if ($object_id != -1) {
								$_echo .= '</div>';
							}
							$object = $eqLogic->getObject();
							if (is_object($object)) {
								if ($object->getConfiguration('useCustomColor') == 1) {
									$_echo .= '<span class="label cursor displayObject" data-object_id="o' . $eqLogic->getObject_id() . '" style="background-color:' . $object->getDisplay('tagColor') . ';color:' . $object->getDisplay('tagTextColor', 'white') . '">' . $object->getName() . ' <i class="fas fa-arrow-circle-right"></i></span>';
								} else {
									$_echo .= '<span class="label cursor displayObject labelObjectHuman" data-object_id="o' . $eqLogic->getObject_id() . '">' . $object->getName() . ' <i class="fas fa-arrow-circle-right"></i></span>';
								}
							} else {
								$_echo .= '<span class="label label-default cursor displayObject" data-object_id="o' . $eqLogic->getObject_id() . '>' . __('Aucun', __FILE__) . ' <i class="fas fa-arrow-circle-right"></i></span>';
							}
							$_echo .= '<br/>';
							$_echo .= '<div class="cmdList" data-object_id="o' . $eqLogic->getObject_id() . '" style="display:none;margin-left : 20px;">';
						}
						$class = 'history';
						if (!$eqLogic->getIsEnable()) $class = 'history disabled';
						$_echo .= '<li class="cursor li_history" data-cmd_id="' . $cmd->getId() . '"><a class="'.$class.'"><i class="far fa-trash-alt remove" title="{{Supprimer tout ou partie de cet historique.}}"></i> ';
						if ($cmd->getSubType() == 'string') {
							$_echo .= '<i class="fas fa-share export" title="{{Exporter cet historique.}}"></i> ';
						}
						$_echo .= $cmd->getEqLogic()->getName() . ' - ' . $cmd->getName() . '</a></li>';
						echo $_echo;
						$object_id = $eqLogic->getObject_id();
					}
					?>
				</ul>
			</div>

			<div class="col-lg-9 col-md-8 col-sm-7">
				<div class="row">
					<div class="col-lg-3 col-sm-12 center">
						<div class="input-group input-group-sm">
							<input id="in_startDate" class="form-control input-sm in_datepicker roundedLeft" style="width: 90px;" value="<?php echo $date['start'] ?>"/>
							<input id="in_endDate" class="form-control input-sm in_datepicker" style="width: 90px;" value="<?php echo $date['end'] ?>"/>
							<a class="btn btn-success btn-sm roundedRight" id='bt_validChangeDate' title="{{Attention : une trop grande plage de dates peut mettre très longtemps à être calculée ou même ne pas s'afficher.}}">
								<i class="fas fa-check"></i>
							</a>
						</div>
					</div>
					<div class="col-lg-7 col-sm-12">
						<center>
							<div class="input-group input-group-sm">
								<select class="fullCorner" id="sel_groupingType" style="width: 180px;">
									<option value="">{{Aucun groupement}}</option>
									<option value="sum::hour">{{Somme par heure}}</option>
									<option value="average::hour">{{Moyenne par heure}}</option>
									<option value="low::hour">{{Minimum par heure}}</option>
									<option value="high::hour">{{Maximum par heure}}</option>
									<option value="sum::day">{{Somme par jour}}</option>
									<option value="average::day">{{Moyenne par jour}}</option>
									<option value="low::day">{{Minimum par jour}}</option>
									<option value="high::day">{{Maximum par jour}}</option>
									<option value="sum::week">{{Somme par semaine}}</option>
									<option value="average::week">{{Moyenne par semaine}}</option>
									<option value="low::week">{{Minimum par semaine}}</option>
									<option value="high::week">{{Maximum par semaine}}</option>
									<option value="sum::month">{{Somme par mois}}</option>
									<option value="average::month">{{Moyenne par mois}}</option>
									<option value="low::month">{{Minimum par mois}}</option>
									<option value="high::month">{{Maximum par mois}}</option>
									<option value="sum::year">{{Somme par année}}</option>
									<option value="average::year">{{Moyenne par année}}</option>
									<option value="low::year">{{Minimum par année}}</option>
									<option value="high::year">{{Maximum par année}}</option>
								</select>

								<select class="fullCorner" id="sel_chartType" style="width: 100px;">
									<option value="line">{{Ligne}}</option>
									<option value="area">{{Aire}}</option>
									<option value="column">{{Barre}}</option>
								</select>

								<span>{{Variation}} <input type="checkbox" id="cb_derive" /></span>
								<span>{{Escalier}} <input type="checkbox" id="cb_step" /></span>

								<span>{{Comparer}} </span>
								<select class="fullCorner" id="sel_compare" style="width: 60px;">
									<?php
										$options = '<option>0</option>';
										for ($o = 1; $o <= 12; $o++) {
											$options .= '<option>-'.$o.'</option>';
										}
										echo $options;
									?>
								</select>
							</div>
						</center>
					</div>
					<div class="col-lg-2 col-sm-12">
						<a class="btn btn-warning pull-right" id='bt_clearGraph' title="{{Vide l'affichage des courbes sur la zone.}}" >
							<i class="fas fa-times"></i> {{Affichage}}
						</a>
					</div>
				</div>
				<div id="div_graph" style="margin-top: 10px;height:calc(100% - 40px)"></div>
			</div>
		</div>
	</div>
	<div role="tabpanel" class="tab-pane" id="timelinetab">
		<div id="timelineOptions" class="reportModeHidden">
			<div class="input-group pull-right" style="display:inline-flex">
				<span class="input-group-btn">
					<select class="form-control roundedLeft input-sm" style="width:300px;" id="sel_timelineFolder">
						<?php
						foreach ((timeline::listFolder()) as $folder) {
							if($folder == 'main'){
								echo '<option value="'.$folder.'">{{Principal}}</option>';
							}else{
								echo '<option value="'.$folder.'">'.$folder.'</option>';
							}
						}
						?>
					</select>
					<a class="btn btn-sm btn-success" id="bt_refreshTimeline"><i class="fas fa-sync"></i> {{Rafraîchir}}
					</a><a id="bt_openCmdHistoryConfigure2" class="btn btn-default btn-sm roundedRight"><i class="fas fa-cogs"></i> {{Configuration}}</a>
				</span>
			</div>
		</div>
		<div id="timelineContainer">
			<ul>
			</ul>
		</div>
	</div>
</div>

<?php
include_file("desktop", "history", "js");
include_file('3rdparty', 'moment/lib.moment', 'js');
?>

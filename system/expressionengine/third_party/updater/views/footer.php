<br clear="all">

<div class="utable hidden" id="error_log">
	<h2><?=lang('error')?></h2>
	<div class="body"></div>
</div>


<div class="utable hidden" id="queries_executed">
	<h2><?=lang('queries_executed')?></h2>
	<div style="padding:20px"><textarea></textarea></div>
</div>


<div class="modal fade" id="test_transfer_method">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3 style="margin-bottom:0px"><?=lang('test_transfer_method')?></h3>
	</div>
	<div class="modal-body">
		<div class="wrapper"></div>
		<p class="loading"><?=lang('loading_wait')?></p>
	</div>
	<div class="modal-footer">
		<a href="#" class="submit" data-dismiss="modal"><?=lang('close')?></a>
	</div>
</div>

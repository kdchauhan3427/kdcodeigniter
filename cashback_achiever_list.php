<div class="panel-body" id="demo_s">
    <table id="demo-table" class="table table-striped"  data-pagination="true" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search="true" >
        <thead>
            <tr>
                <th>#</th>
                <th><?php echo translate('customer');?></th>
                <th><?php echo translate('cashback_amount');?></th>
                <th><?php echo translate('achieved_date');?></th>
                <th><?php echo translate('wallet (current)');?></th>
                
            </tr>
        </thead>				
        <tbody >
        <?php
            $i = 0;
            // echo json_encode($achiever);die;
            foreach($achiever as $row){
                // echo '<pre>';print_r($row);die;
                $cashback_data = json_decode($row['cashback_data']);
                // echo '<pre>';print_r($cashback_data->cashback_date);die;
                $i++;
        ?>                
        <tr>    
            <td>
                <?= $i; ?>
            </td>            
            <td>
                <a class="btn btn-dark btn-xs btn-labeled fa fa-user" data-toggle="tooltip" 
                    onclick="ajax_modal('user_view','<?php echo translate('view_profile'); ?>','<?php echo translate('successfully_viewed!'); ?>','user_view','<?php echo $row['user_id']; ?>')" data-original-title="View" data-container="body">
                        <?php  $lkk = $this->db->get_where('user',array('user_id'=>$row['user_id']))->row();echo $lkk->username; ?>
                </a>
            </td>
            <td><?php echo currency(5000); ?></td>
            <td><?php echo date('d M,Y',$cashback_data->cashback_date); ?></td>
            <td><?php echo currency(round(base64_decode($row['wallet']))); ?></td>
        </tr>
        <?php
            }
        ?>
        </tbody>
    </table>
</div>
    <div id="vendr"></div>
    <div id='export-div' style="padding:40px;">
		<h1 id ='export-title' style="display:none;"><?php echo translate('cashback_achievers'); ?></h1>
		<table id="export-table" class="table" data-name='cashback_achievers' data-orientation='p' data-width='1500' style="display:none;">
				<colgroup>
					<col width="50">
					<col width="150">
					<col width="150">
                    <col width="150">
                    <col width="150">
				</colgroup>
				<thead>
					<tr>
						<th><?php echo translate('no');?></th>
                        <th><?php echo translate('customer');?></th>
                        <th><?php echo translate('cashback_amount');?></th>
                        <th><?php echo translate('achieved_date');?></th>
                        <th><?php echo translate('wallet (current)');?></th>
					</tr>
				</thead>



				<tbody >
				<?php
					$i = 0;
	            	foreach($achiever as $row){
                        $cashback_data = json_decode($row['cashback_data']);
	            		$i++;
				?>
				<tr>
					<td>
                        <?= $i; ?>
                    </td>            
                    <td>
                        <?php  $lkk = $this->db->get_where('user',array('user_id'=>$row['user_id']))->row();echo $lkk->username; ?>
                    </td>
                    <td><?php echo currency(5000); ?></td>
                    <td><?php echo date('d M,Y',$cashback_data->cashback_date); ?></td>
                    <td><?php echo currency(round(base64_decode($row['wallet']))); ?></td>      	
				</tr>
	            <?php
	            	}
				?>
				</tbody>
		</table>
	</div>
           
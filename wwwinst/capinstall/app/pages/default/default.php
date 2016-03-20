	
<?php $help = 0; ?>
<div id="status" class="row">
		<div class="span7">
			<h2>Status</h2>
			<div class="well">
			<table class="table table-striped">
				<tbody>
				<tr>
					<td><strong>CapAnalysis</strong></td>
					<td><i class="icon-chevron-right"></i></td>
					<td>
					<?php if ($running) : ?>
						<span class="label label-success">Running</span>
					<?php else : $help = 1; ?>
						<span class="label label-important">Stopped</span>
					<?php endif; ?>
					</td>
				</tr>
				<?php if ($help == 0) : ?>
				<tr>
					<td><strong>Database</strong></td>
					<td><i class="icon-chevron-right"></i></td>
					<td>
					<?php if ($db_con == True && $db_tables == True) : ?>
						<span class="label label-success">Ok</span>
					<?php else :  $help = 4; ?>
						<?php if ($db_usr == False) : ?>
						<span class="label label-important">User autentication failed</a>
						<?php else : if ($db_tables == False) : ?>
						<span class="label label-important">DB not present</a>
						<?php else : ?>
						<span class="label label-important">Connection</a>
						<?php endif; ?>
						<?php endif; ?>
					<?php endif; ?>
					</td>
				</tr>
				<?php endif; ?>
				</tbody>
			</table>
			</div>
		</div>
		<div class="span1">
		</div>
		<div class="span4">
			<?php if ($help) : ?>
			<h3>Install-Execution-Advice</h3>
			<?php switch ($help):
				case 1: ?>
				<p>To start CapAnalysis open a terminal-shell and use the command: </p>
				<code>sudo service capanalysis start</code>
				<p>or:</p>
				<code>sudo systemctl start capanalysis.service</code>
				<p>and after reload this page.</p>
				<?php break; ?>
			<?php case 4: ?>
				<?php if ($db_con == False): ?>
				<p>PostgreSQL <span class="label label-important">is not running</span>.</p>
				<p>
				To fix the problem restart PostgreSQL daemon:<br/>
                <code>sudo service postgresql restart</code>
				<p>or:</p>
				<code>sudo systemctl start postgresql.service</code>
				</p>
				<?php else: if ($db_usr == False): ?>
				<p>CapAnalysis <span class="label label-important"> the user DB failed the authentication</span>.</p>
				<p>
				To change the user password click the button below.
				</p>
				<p>
				<a class="btn btn-large btn-primary" type="button" href="<?php echo $ROOT_APP.'createdb'; ?>">New password</a>
				</p>
				<?php else: ?>
				<p>CapAnalysis <span class="label label-important">database doesn't seem to be present</span>.</p>
				<p>
				To create a new CapAnalysis DB click the button below.
				</p>
				<p>
				<a class="btn btn-large btn-primary" type="button" href="<?php echo $ROOT_APP.'createdb'; ?>">Create DB</a>
				</p>
				<?php endif ?>
				<?php endif ?>
				<?php break; ?>
			<?php endswitch; ?>
			<?php else: ?>
				<p style="padding: 110px 0 0 0;">
                <a class="btn btn-large btn-success" type="button" href="<?php echo $ROOT_APP.'../'; ?>">Go to CapAnalysis UI</a>
				</p>
			<?php endif; ?>
		</div>
</div>

<style>

</style>

<h1>Sélection des noeuds pour l'export XML</h1>

<p>
	Sélectionnez les contenus à exporter en XML.<br/>
	Pour chaque noeud à exporter, vous devez saisir le node id du noeud parent dans lequel ce noeud devra être importé.<br/>
	Attention : les objets contenant des attributs de type relations d'objet doivent être exportés EN DERNIER !<br/>

</p>

<form action={"xmlexport/selection"|ezurl()} method="POST" name="exportselectionform">
<div class="block">

	<table class="list" cellspacing="0">
		<tr>
			
			<th style="width:30%;">Noeud à exporter</th>
			<th >Node Id parent destination</th>
			<th style="width:8%;"></th>
		</tr>

		{if $nodeList|count()}
			{def $node=''}
			{foreach $nodeList as $i => $nodeId}
			    {set $node=fetch( 'content', 'node', hash( 'node_id', $nodeId ))}

			    <tr>
			    	
			    	<td>{$node.name} [{$nodeId}]
	    				<input type="hidden" value="{$nodeId}"  name="exportNodeIds[]" />
	    			</td>
			    	<td>
			    		<input type="text" value="{$destinationNodeIds[$nodeId]}"  name="{concat('destinationNodeIds[',$nodeId,']')}"/>
			    	</td>
			    	<td>
			    		<a href="#" class="moveUpButton"><image src={"up.png"|ezimage()} /></a>
			    		<a href="#" class="moveDownButton"><image src={"down.png"|ezimage()} /></a>
			    		<a href="#" class="deleteButton"><image src={"delete.png"|ezimage()} /></a>
			    	</td>

			    </tr>
			{/foreach}
		{/if}

	</table>

{*if $nodeList|count()}

	<div style="float:left;font-weight:bold;width:350px;">
		Noeud à exporter
	</div>

	<div style="float:left;font-weight:bold;width:250px;">
		Node Id parent destination
	</div>	

	{def $node=''}
	{foreach $nodeList as $i => $nodeId}
	    {set $node=fetch( 'content', 'node', hash( 'node_id', $nodeId ))}

	    <div style="float:left;width:350px;clear:both;margin-top:20px;">
	    	{$node.name} [{$nodeId}]
	    	<input type="hidden" value="{$nodeId}"  name="{concat('exportNodeIds[',$i,']')}" />
	    </div>

	    <div>
	    	<input type="text" value="{$destinationNodeIds[$nodeId]}"  style="float:left;width:30px;margin-top:20px;" name="{concat('destinationNodeIds[',$nodeId,']')}"/>
	    </div>

	{/foreach}

	</ul>

{else}
	Aucun noeud sélectionné pour l'export
{/if*}

<div style="clear:both;padding-top:20px;">
	<input class="button" type="submit" title="Ajouter des noeuds" value="Ajouter des noeuds" name="AddButton">
	<input class="button" type="submit" title="Exporter en XML" value="Exporter en XML" name="ExportButton">
</div>


</div>


</form>



{literal}
<script type="text/javascript">
	$('.moveUpButton').click(function(){
	  var current = $(this).parent().parent();
	  current.prev().before(current);
	});

	$('.moveDownButton').click(function(){
	  var current = $(this).parent().parent();
	  current.next().after(current);
	});

	$('.deleteButton').click(function(){
	  var current = $(this).parent().parent();
	  current.remove();
	});


</script>


{/literal}
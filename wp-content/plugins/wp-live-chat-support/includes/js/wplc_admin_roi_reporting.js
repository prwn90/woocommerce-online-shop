var date_data_array = new Array();
var agent_data_array = new Array();

var current_goal = null;

jQuery(function(){
		
		jQuery('a[href=#rio_reports]').click(function(){
			jQuery('#wplc_roi_report_list_item_0').trigger( "click" );
		});

		jQuery('#wplc_roi_report_date_selector').on('change', function(){
			if(current_goal !== null){
				var goal_id = current_goal;
				var data = {
					'action': 'get_goal_data',
					'goal_id': goal_id,
					'term': jQuery(this).val()
				}

				wplc_roi_get_data(data);
			}
		});

		jQuery('.wplc_roi_report_list_item').click(function(){
			var goal_id = jQuery(this).attr('goal');
			current_goal = goal_id;
			var data = {
				'action': 'get_goal_data',
				'goal_id': goal_id,
				'term': jQuery('#wplc_roi_report_date_selector').val()
			}

			wplc_roi_get_data(data);
		});

		function wplc_roi_get_data(data){
			jQuery.post( ajaxurl, data, function(response){
				var json_data = JSON.parse(response);
				
				jQuery('.wplc_roi_report_content_inner').html(json_data.html);

				date_data_array = new Array();
				if(typeof json_data.date_array !== "undefined"){
					var counter = 0;
					for(var key in json_data.date_array){
						var pretty_date = json_data.date_array[key]['date'].substring(json_data.date_array[key]['date'].indexOf("-")+1);
						pretty_date = pretty_date.replace("-", "/");

						date_data_array[counter] = new Array();
						date_data_array[counter].push(pretty_date);
						date_data_array[counter].push(json_data.date_array[key]['value']);
						counter ++;
					}
					if(counter > 0){
						wplc_draw_roi_date_chart();
					}
				}

				agent_data_array = new Array();
				if(typeof json_data.agent_array !== "undefined"){
					var counter = 0;
					for(var key in json_data.agent_array){
						agent_data_array[counter] = new Array();
						agent_data_array[counter].push(json_data.agent_array[key]['name']);
						agent_data_array[counter].push(json_data.agent_array[key]['value']);
						counter ++;
					}
					if(counter > 0){
						wplc_draw_roi_agent_chart();
					}
				}
			});
		}
});



function wplc_draw_roi_date_chart(){
	var data = new google.visualization.DataTable();
    data.addColumn('string', 'Content');
    data.addColumn('number', 'Value');
    data.addRows(date_data_array);

	var options = {
  		vAxis: {minValue: 0},
  		animation:{
  			duration: 500,
  			startup: true
  		},
  		backgroundColor: 'transparent',
  		legend: 'none',
  		colors : ['#0073AA','#757575'],
  		hAxis:{
  			textStyle: {
  				fontSize: "9"
  			},
  			slantedText: false
  		},
  		vAxis:{
  			textStyle: {
  				fontSize: "12"
  			},
  			format: 'short'
  		},
  		chartArea:{
  			left: 50,
  			top:30,
  			right:20,
  			bottom:30
  		},
  		crosshair:{
  			trigger: 'both',
  			orientation: 'vertical',
  			opacity: 0.5,
  			color: "#0073AA"
  		},
  		focusTarget : 'category'
	};

    var chart = new google.visualization.AreaChart(document.getElementById('wplc_roi_grid_chart'));
	chart.draw(data, options);
	 
}


function wplc_draw_roi_agent_chart(){
	var data = new google.visualization.DataTable();
    data.addColumn('string', 'Content');
    data.addColumn('number', 'Value');
    data.addRows(agent_data_array);

	var options = {
		    bar: {groupWidth: "10%"},
		    legend: "none",
		    colors : ['#0073AA','#757575'],
		    height:270,
		    chartArea:{
          			left: 50,
          			top:30,
  					right: 50,
  					bottom:30
          	},
          	animation:{
          			duration: 500,
          			startup: true
          	},
  	};

    var chart = new google.visualization.ColumnChart(document.getElementById('wplc_roi_agent_chart'));
	chart.draw(data, options);
	 
}
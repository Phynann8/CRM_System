
	function createAbsoluteHidden(){
		var div = document.createElement("div");
		div.setAttribute("id", "cam-app-absolute");
		div.style.width = "210mm";
		div.style.minHeight  = "296mm";
		div.style.position   = "absolute";
		div.style.zIndex    = "-1000";
		div.style.bottom    = "0";
		document.body.appendChild(div);
	}
	function captureAndDownloadImage(node,index,title="achievement"){
		
		setTimeout(function() {
			domtoimage.toPng(node).then(function (dataUrl) {
				var link = document.createElement('a');
				link.download = title+index+$("#title-"+index).val();
				link.href = dataUrl;
				link.click();
			}).catch(function (error) {
				console.error('oops, something went wrong!', error);
			});
		}, 100);
	}
	
	function downloadAsImage(urlGet,contentData,title="achievement"){
		createAbsoluteHidden();
		$.ajax({
			type: 'POST',
			url: urlGet,
			data: contentData,
			success: function(data) {
				if(data!=""){
					$("#cam-app-absolute").html(data);
					setTimeout(function() {
						let pages = document.querySelectorAll('.html-content-holder');
						var index=0;
						for (let node of pages) { 
							captureAndDownloadImage(node,index,title);
							index++;
						}
					}, 500);
				}
				
			}, error: function(err) {
			}
		});
	}


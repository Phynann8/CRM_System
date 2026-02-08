	const curDate = new Date();
	const content = document.getElementById('divPrint');
	const mainViewer = document.getElementById('main-viewer');
	const viewer = document.getElementById('viewer');
	const tableBody = document.getElementById('tableBody');
	const zoomPercent = document.getElementById('zoomPercent');
	const pageIndicator = document.getElementById('pageIndicator');

	let zoom = 1;
	let currentPage = 0;
	let totalPages = 1;
	let pageHeight = 1123; // in px
	let colForExport = [];

	document.addEventListener("DOMContentLoaded", function(e) {
	  setTimeout(function() {
			var currentTitle = $(document).attr('title');
			$(".title-report").html(currentTitle);
		}, 500);
	});	

	function updateViewer(initalpage=0) {
		if(initalpage>0){
			currentPage = initalpage-1;
			content.style.top = `-${currentPage * pageHeight}px`;
			zoomPercent.textContent = `${Math.round(zoom * 100)}%`;
			//viewer.style.transform = `scale(${zoom})`;
			mainViewer.style.transform = `scale(${zoom})`;
			$("#pageIndicatorValue").val(initalpage);
		}else{
			content.style.top = `-${currentPage * pageHeight}px`;
			zoomPercent.textContent = `${Math.round(zoom * 100)}%`;
			//viewer.style.transform = `scale(${zoom})`;
			mainViewer.style.transform = `scale(${zoom})`;
			$("#pageIndicatorValue").val((currentPage+1));
		}
		
		const scaledPageHeight = pageHeight * zoom;

		const contentHeight = content.scrollHeight;
		totalPages = Math.ceil(contentHeight / scaledPageHeight);
		pageIndicator.textContent = ` / ${totalPages}`;
		
		
	}
	
	document.getElementById('pageIndicatorValue').onchange = () => {
		var thisPageValue = $("#pageIndicatorValue").val();
		if (thisPageValue > totalPages ) {
			thisPageValue = totalPages;
		}
		updateViewer(thisPageValue);
		
	};

	document.getElementById('zoomIn').onclick = () => {
		zoom = Math.min(2, zoom + 0.1);
		updateViewer();
	};

	document.getElementById('zoomOut').onclick = () => {
		zoom = Math.max(0.5, zoom - 0.1);
		updateViewer();
	};

	document.getElementById('nextPage').onclick = () => {
		if (currentPage < (totalPages-1) ) {
			currentPage++;
			updateViewer();
		}
	};

	document.getElementById('prevPage').onclick = () => {
		if (currentPage > 0) {
		  currentPage--;
		  updateViewer();
		}
	};
	  
	document.getElementById('portraitBtn').onclick = () => {
		setPrintOrientation('portrait');
		if(currentPage>totalPages){
			updateViewer(totalPages);
		}
	};
	document.getElementById('landscapeBtn').onclick = () => {
		setPrintOrientation('landscape');
		if(currentPage>totalPages){
			updateViewer(totalPages);
		}
		
	};
  
	function setPrintOrientation(orientation = 'portrait',papersize='A4',isCustomePrint=false) {
		$("#printPageSize").remove();
		
		if(!isCustomePrint){
			/**
			isCustomePrint = true @page យកតាម style នៃ page នីមួយៗ
			**/
			const cssText = `
				@page { size: ${papersize} ${orientation}; }
			`;
		
			const style = document.createElement('style');
			style.id = "printPageSize";
			style.media = 'print'; 
			style.appendChild(document.createTextNode(cssText));
			document.getElementById('divPrint').appendChild(style);
		}
		
		
		if(orientation=='portrait'){
			pageHeight = 1123;
			
			document.getElementById("main-viewer").style.width = "865px";
			document.getElementById("main-viewer").style.height = "1123px";
			
			viewer.classList.remove('landscape');
			mainViewer.classList.remove('landscape');
			viewer.classList.add('portrait');
			mainViewer.classList.add('portrait');
	
			document.getElementById("landscapeBtn").classList.remove("active");
			document.getElementById("portraitBtn").classList.add("active");
		}else{
			pageHeight = 794;
			
			document.getElementById("main-viewer").style.width = "1160px";
			document.getElementById("main-viewer").style.height = "865px";
			
			viewer.classList.remove('portrait');
			mainViewer.classList.remove('portrait');
			viewer.classList.add('landscape');
			mainViewer.classList.add('landscape');
	
			document.getElementById("landscapeBtn").classList.add("active");
			document.getElementById("portraitBtn").classList.remove("active");
		}
		updateViewer();
	}
	
	const toggleBtn = document.getElementById('togglePanel');
	const rightPanel = document.getElementById('rightPanel');
	const mainContent = document.getElementById('mainContent');

	let isOpen = false;
	toggleBtn.onclick = () => {
		closeRightPanel();
	};
	function closeRightPanel(){
		isOpen = !isOpen;
		rightPanel.classList.toggle('open', isOpen);
		mainContent.style.width = isOpen ? '75%' : '100%';
	}
	
	function preview() {

		var currentTitle = $(document).attr('title');
		var thisDate = curDate.getDate();
		var thisMonth = curDate.getMonth()+1;
		var thisYear = curDate.getFullYear();
		
		var todayStr = thisDate+'-'+thisMonth+'-'+thisYear;
		currentTitle = currentTitle+'-'+todayStr;
		
		var disp_setting = "toolbar=no,status=no,resizable=no,location=no,directories=yes,menubar=no,";
		disp_setting += "scrollbars=no,fullscreen=yes, height=700, left=100, top=25";
		var content_vlue = document.getElementById("divPrint").innerHTML;
		var docprint = window.open("", "", disp_setting);
		docprint.document.open();
		docprint.document.write('<html><head><title>'+currentTitle+'</title>');
		docprint.document.write('</head><div style=" font-size:16px !important; margin:0px; font-family:Verdana;"><style>table th {font-size:14px !important;} table td{font-size:12px !important;}</style><center>');
		docprint.document.write(content_vlue);
		docprint.document.write('</center></div></html>');
		docprint.document.close();
		docprint.focus();
	}
				
	function exportExcel(contentId="exportExcel",title="") {
		var currentTitle = $(document).attr('title');
		var thisDate = curDate.getDate();
		var thisMonth = curDate.getMonth()+1;
		var thisYear = curDate.getFullYear();
		
		var todayStr = thisDate+'-'+thisMonth+'-'+thisYear;
		currentTitle = currentTitle+'-'+todayStr;
		if(title!=""){
			currentTitle = title;
		}
		
		
		if(colForExport.length > 0){
			colForExport.forEach(col => {
				const selector = `td.hideExport[data-col="${col}"]`;
				const selectorExp = `td.showExport[data-col="${col}"]`; 
				var isHidden = $('td[data-col="'+col+'"]').css('display') === 'none';
				if (isHidden) {
					$(selector).hide();
					$(selectorExp).hide();
				}else{
					$(selector).hide();
					$(selectorExp).css("display", "table-cell");
				}
			});
		}else{
			$(".hideExport").css("display", "none");
			$(".showExport").css("display", "table-cell");
		}
		
		loadingBlock();
		setTimeout(function() {
			$('#'+contentId).tableExport({
				type: 'excel'
				,escape: 'false'
				,fileName:currentTitle
			});
		
			if(colForExport.length > 0){
				colForExport.forEach(col => {
					const selector = `td[data-col="${col}"]`;
					const selectorExp = `td.showExport[data-col="${col}"]`; 
					var isHidden = $('td[data-col="'+col+'"]').css('display') === 'none';
					if (isHidden) {
						$(selector).hide();
						$(selectorExp).hide();
					}else{
						$(selector).css("display", "table-cell");
						$(selectorExp).hide();
					}
				});
			}else{
				$(".hideExport").css("display", "table-cell");
				$(".showExport").css("display", "none");
			}
			HideloadingBlock();
		}, 500);
	}
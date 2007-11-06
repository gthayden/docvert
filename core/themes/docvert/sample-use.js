addEvent(window, "load", initialiseUploads);
addEvent(window, "load", initialiseThumbnails);
addEvent(window, "load", initializeAutoPipelines);
addEvent(window, "load", setPreviewHeight);
addEvent(window, "load", chooseCssOnUnstyledDocument);
addEvent(window, "resize", setPreviewHeight);

var usedXmlHttpRequests = new Array();

var indexCounter = 0;

function addmore()
	{
	indexCounter++;
	var additionalUploadsDiv = document.getElementById('additionalUploads');
	var divTag = document.createElement("div");
	divTag.style.margin = "0px";
	var inputTag = document.createElement("input");
	inputTag.type = "file";
	inputTag.name = "random"+indexCounter;
	inputTag.className = "upload";
	divTag.appendChild(inputTag);
	additionalUploadsDiv.appendChild(divTag);
	addEvent(inputTag, "change", addmore);
	}

function initialiseUploads(event)
	{
	var inputElements = document.getElementsByTagName("input");
	for(var inputElementsIndex = 0; inputElementsIndex < inputElements.length; inputElementsIndex++)
		{
		var inputElement = inputElements[inputElementsIndex];
		if(inputElement)
			{
			if(inputElement.getAttribute("type") == "file")
				{
				addEvent(inputElement, "change", addmore);
				}
			}
		}
	}

function initializeAutoPipelines()
	{
	checkForAutoPipeline(document.getElementById('pipeline'));
	}

function initialiseThumbnails(event)
	{
	var tabsDiv = document.getElementsByTagName("tabs");
	if(tabsDiv)
		{
		var tabHyperlinks = document.getElementsByTagName("a");
		for(var tabHyperlinkIndex = 0; tabHyperlinkIndex < tabHyperlinks.length; tabHyperlinkIndex++)
			{
			var tabHyperlink = tabHyperlinks[tabHyperlinkIndex];
			if(tabHyperlink.id)
				{
				if(tabHyperlink.id.substring(0, 13) == "thumbnaillink")
					{
					addEvent(tabHyperlink, "mouseover", thumbnailOver);
					addEvent(tabHyperlink, "mouseout", thumbnailOut);
					}
				else
					{
					//alert("not a preview hyperlink " + tabHyperlink.id);
					}
				}
			else
				{
				//alert("no id on hyperlink");
				}

			}
		}
	}

function thumbnailOver(event)
	{
	var thumbnailDiv = document.getElementById("thumbnail");
	var sender = getSenderByEvent(event);
	
	if(thumbnailDiv && sender)
		{
		elementPosition = findPos(sender);
		var thumbnailPageId = sender.id.replace("link", "");
		var thumbnailImage = document.getElementById(thumbnailPageId);
		
		if(thumbnailImage)
			{
			thumbnailDiv.style.left = (elementPosition[0] + (sender.clientWidth/2) - 60) + "px";
			thumbnailDiv.style.top = (elementPosition[1] + 30) + "px";
			thumbnailDiv.style.background = "white url(\"" + thumbnailImage.src + "\")";
			thumbnailDiv.style.display = "block";
			}
		else
			{
			//alert("could not find thumbnail image of #" + thumbnailPageId);
			}
		}
	else
		{
		//alert("Could not find thumbnail");
		}		
	}


function getSenderByEvent(event)
	{
	var sender = event.target;
	if(!sender) sender = event.srcElement;
	return sender;
	}

function thumbnailOut(event)
	{

	var thumbnailDiv = document.getElementById("thumbnail");
	var sender = getSenderByEvent(event);
	if(thumbnailDiv && sender)
		{
		thumbnailDiv.style.display = "none";
		}
	}



function checkForAutoPipeline(sender)
	{
	if(sender)
		{
		var autoPipelineBreakUpOver = document.getElementById('breakUpOver');
		autoPipelineBreakUpOver.style.display = "block";
		var theOption = sender.options[sender.options.selectedIndex].value;
		if(theOption.indexOf('autopipeline:') >= 0)
			{
			sender.className = "autopipeline";
			autoPipelineBreakUpOver.style.visibility = "visible";
			}
		else
			{
			sender.className = "regularpipeline";
			autoPipelineBreakUpOver.style.visibility = "hidden";
			}
		}
	}

// addEvent cross-browser for IE5+,  NS6 and Mozilla
// By Scott Andrew
function addEvent(sender, eventType, callBackFunction, useCapture)
	{
  	if (sender.addEventListener)
		{
		sender.addEventListener(eventType, callBackFunction, useCapture);
		return true;
		}
	else if (sender.attachEvent)
		{
		var r = sender.attachEvent("on"+eventType, callBackFunction);
		return r;
		}
	}

function setPreviewHeight(event)
	{
	var thePreviewIFrame = document.getElementById("previewIFrame");
	if(thePreviewIFrame)
		{
		var minimumHeight = 30;
		var browserHeight = 0;
		if(window.innerHeight)
			{
			browserHeight = window.innerHeight;
			}
		else if(document.body.clientHeight)
			{
			browserHeight = document.body.clientHeight;
			}
		var newHeight = browserHeight - 294;
		if(newHeight < minimumHeight)
			{
			newHeight = minimumHeight;
			}
		thePreviewIFrame.style.height = newHeight + "px";
		}
	}
	
function closeUploadDialog()
	{
	resetLoadingScreen();
	return setUploadDialog("none");
	}


function openUploadDialog()
	{
	return setUploadDialog("block");
	}


function setUploadDialog(styleDisplay)
	{
	var uploadDialog = document.getElementById("uploadDialog");
	if(uploadDialog)
		{
		uploadDialog.style.display = styleDisplay;
		return false;
		}
	}

function revealLoadingScreen()
	{
	var uploadingProgress = document.getElementById("uploadprogress");
	if(uploadingProgress)
		{
		uploadingProgress.style.display = "block";
		}
	}

function resetLoadingScreen()
	{
	var uploadingProgress = document.getElementById("uploadprogress");
	if(uploadingProgress)
		{
		uploadingProgress.src = "loading.php";
		uploadingProgress.style.display = "none";
		}			

	}

function changeTab(sender)
	{
	var tabDiv = document.getElementById("tabs");
	var editDocumentLink = document.getElementById("editDocumentLink");
	if(tabDiv && editDocumentLink)
		{
		var tabs = tabDiv.getElementsByTagName('li')
		for(var tabIndex = 0; tabIndex < tabs.length; tabIndex++)
			{
			var tab = tabs[tabIndex];
			tab.className = "";
			}
		sender.parentNode.className = "current";
		editDocumentLink.style.visibility = "visible";
		}
	}



/* thanks to quirksmode.org */
function findPos(obj)
	{
	var curleft = curtop = 0;
	if (obj.offsetParent)
		{
		curleft = obj.offsetLeft;
		curtop = obj.offsetTop;
		while (obj = obj.offsetParent)
			{
			curleft += obj.offsetLeft;
			curtop += obj.offsetTop;
			}
		}
	return [curleft,curtop];
	}



function toggleCustomPort()
	{
	var defaultPort = document.getElementById("defaultPort");
	var customPort = document.getElementById("customPortContainer");
	if(customPort)
		{
		if(defaultPort.checked)
			{
			customPort.style.display = "none";
			}
		else
			{
			customPort.style.display = "inline";
			}
		}
	}


function editThisDocument(sender)
	{
	sender.style.visibility = "hidden";
	var theIframe = document.getElementById("previewIFrame");
	var iframeDocument = theIframe.contentDocument;
	var contentFrameDocument = iframeDocument.getElementById("contentFrame").contentDocument;
	var currentLocationString = contentFrameDocument.location.toString()
	var newLocationString = currentLocationString.substring(0, currentLocationString.lastIndexOf('/') + 1) + "docvert--all-html.html";
	contentFrameDocument.location = newLocationString;
	return false;
	}



function reallyReplace(subject, searchString, replaceString)
	{
	var exitAfterXLoops = 1000;

	while(subject.indexOf(searchString) >= 0)
		{
		subject = subject.replace(searchString, replaceString);
		exitAfterXLoops -= 1;
		if(exitAfterXLoops < 1) break;
		}
	return subject;
	}

function setEditorContent(instanceName, contentFrameDocument, content)
	{
	if(contentFrameDocument.defaultView.FCKeditorAPI)
		{
		fckInstance = contentFrameDocument.defaultView.FCKeditorAPI.GetInstance(instanceName);
		if(fckInstance)
			{
			fckInstance.SetHTML(content);
			}
		}
	}

function onSubmitUpdateContent()
	{
	var theIframe = document.getElementById("previewIFrame");
	if(theIframe)
		{
		var iframeDocument = theIframe.contentDocument;
		var contentFrameDocument = iframeDocument.getElementById("contentFrame").contentDocument;
		if(contentFrameDocument.defaultView.FCKeditorAPI)
			{
			var instanceName = "contentValue";
			fckInstance = contentFrameDocument.defaultView.FCKeditorAPI.GetInstance(instanceName);
			if(fckInstance)
				{
				var passBackElement = contentFrameDocument.getElementById(instanceName + "passBack");
				var passBackForm = contentFrameDocument.getElementById(instanceName + "passBackForm");
				if(passBackElement && passBackForm)
					{
					passBackElement.value = fckInstance.GetHTML();
					passBackForm.submit();
					alert("submitted");
					}
				}
			}
		}
	return false;
	}



function chooseCssOnUnstyledDocument()
	{
	var documentHasStyle = false;
	var theIframe = document.getElementById("previewIFrame");
	if(theIframe)
		{
		var iframeDocument = theIframe.contentDocument;
		var contentFrameDocument = iframeDocument.getElementById("contentFrame").contentDocument;
		var styleTags = contentFrameDocument.getElementsByTagName("style");
		if(styleTags)
			{
			documentHasStyle = true;
			}
		var linkTags = contentFrameDocument.getElementsByTagName("link");
		for(var i=0; i < linkTags.length; i++)
			{
			var linkTag = styleTags[i];
			if(linkTag.getAttribute("rel") == "stylesheet")
				{
				documentHasStyle = True;
				}
			}		
		}
	if(documentHasStyle)
		{
		setInterval('setCssOnUnstyledDocument()', 100)
		}
	}

var previousLocation = null;

function setCssOnUnstyledDocument()
	{
	var theIframe = document.getElementById("previewIFrame");
	if(theIframe)
		{
		var iframeDocument = theIframe.contentDocument;
		var contentFrameDocument = iframeDocument.getElementById("contentFrame").contentDocument;
		var headTags = contentFrameDocument.getElementsByTagName("head");
		if(headTags)
			{
			var headTag = headTags[0];
			if(headTag.innerHTML.indexOf("stylesheet") == -1)
				{
				//debugWriteLine("set it");
				headTag.innerHTML += '<link rel="stylesheet" href="../../../core/themes/docvert/preview-screen.css" media="screen"/><link rel="stylesheet" href="../../../core/themes/docvert/preview-print.css" media="print"/>';
				}
			}
		previousLocation = contentFrameDocument.location.toString();
		}
	}

function debugWriteLine(line)
	{
	var debugWriteLineElement = document.getElementById("debugWriteLine");
	if(debugWriteLineElement)
		{
		debugWriteLineElement.style.display = "block";
		debugWriteLineElement.innerHTML += line + "\n<br />";
		}
	}



function submitStage1()
	{
	
	return false;
	}

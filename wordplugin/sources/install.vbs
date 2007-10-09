On Error Resume Next
Set wordApp = GetObject(, "Word.Application")  'attempt to get an existing running copy of Word
If Err Then
	Err.Clear 	'destroy the error
	Set wordApp = CreateObject("Word.Application")
End If
Err.Clear
wordApp.Visible=true
ErrorHandler

If wordApp.CommandBars("Docvert web service") Then
	On Error Resume Next
	wordApp.CommandBars("Docvert web service").Delete
	Err.Clear
	wordApp.CommandBars("Docvert web service").Delete
	Err.Clear
	wordApp.CommandBars("Docvert web service").Delete
	Err.Clear
	wordApp.CommandBars("Docvert web service").Delete
	Err.Clear
	wordApp.CommandBars("Docvert web service").Delete
	Err.Clear
End If
ErrorHandler

Set docvertCommandBar = wordApp.CommandBars.Add("Docvert web service", 1, false, false)  'commandBars.Add(name, barType, boolReplaceToolBar, boolTemporary)
docvertCommandBar.Visible = True
ErrorHandler
Set updateWidgets = docvertCommandBar.Controls.Add(1)
updateWidgets.Style = 2
updateWidgets.Caption = "^"
updateWidgets.TooltipText = "Check Docvert Server for theme updates..."
updateWidgets.OnAction = "updateWidgets"
ErrorHandler
Set pipelinesDropDownList = docvertCommandBar.Controls.Add(3)
pipelinesDropDownList.Caption = "Available themes"
pipelinesDropDownList.Width = 200
pipelinesDropDownList.AddItem "regularpipeline:s5 slideshow"
pipelinesDropDownList.AddItem "regularpipeline:simple webpage"
pipelinesDropDownList.AddItem "regularpipeline:none"
ErrorHandler
Set autoPipelinesDropDownList = docvertCommandBar.Controls.Add(3)
autoPipelinesDropDownList.Caption = "Theme options..."
autoPipelinesDropDownList.Width = 150
ErrorHandler
Set goButton = docvertCommandBar.Controls.Add(1)
goButton.Style = 2
goButton.Caption = "&Docvert It!"
goButton.OnAction = "sendToWebService"
wordApp.Quit
Set wordApp = Nothing
ErrorHandler

Sub ErrorHandler:
	If Err Then
		Dim errorMessage
		errorMessage = Err.Description & vbCrLf & vbCrLf & "(Error #" & Err.Number & ")"
		MsgBox errorMessage, vbCritical, "Docvert Error"
		Err.Clear
	End If
End Sub

'barTypeNormal = 0 'toolbar
'barTypeMenuBar = 1 'menu bar
'barTypePopup = 2 'menu, submenu or shortcut menu

'msoControlCustom = 0
'msoControlButton = 1 ' CommandBarButton
'msoControlEdit = 2 ' CommandBarComboBox
'msoControlDropdown = 3 ' CommandBarComboBox
'msoControlComboBox = 4 ' CommandBarComboBox
'msoControlButtonDropdown = 5 ' CommandBarComboBox
'msoControlSplitDropdown = 6 ' CommandBarComboBox
'msoControlOCXDropdown = 7 ' CommandBarComboBox
'msoControlGenericDropdown = 8
'msoControlGraphicDropdown = 9 ' CommandBarComboBox
'msoControlPopup = 10 ' CommandBarPopup
'msoControlGraphicPopup = 11 ' CommandBarPopup
'msoControlButtonPopup = 12 ' CommandBarPopup
'msoControlSplitButtonPopup = 13 ' CommandBarPopup
'msoControlSplitButtonMRUPopup = 14 ' CommandBarPopup
'msoControlLabel = 15
'msoControlExpandingGrid = 16
'msoControlSplitExpandingGrid = 17
'msoControlGrid = 18
'msoControlGauge = 19
'msoControlGraphicCombo = 20 ' CommandBarComboBox

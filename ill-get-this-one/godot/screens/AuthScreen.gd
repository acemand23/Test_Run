class_name AuthScreen
extends Control
## Login / Register, plus a field to point the app at your server.

var _register_mode := false
var _name_input: LineEdit
var _email_input: LineEdit
var _pass_input: LineEdit
var _server_input: LineEdit
var _status: Label
var _submit: Button
var _toggle: Button
var _title: Label

func _ready() -> void:
	size_flags_horizontal = Control.SIZE_EXPAND_FILL
	var col := UI.vbox(14)
	col.size_flags_horizontal = Control.SIZE_EXPAND_FILL
	add_child(col)

	_title = UI.heading("I'll Get This One")
	col.add_child(_title)
	col.add_child(UI.label("Funny-money points between friends.", 14, UI.MUTED))
	col.add_child(UI.spacer(6))

	_name_input = UI.input("Your name")
	col.add_child(_name_input)

	_email_input = UI.input("Email")
	_email_input.keep_editing_on_text_submit = true
	col.add_child(_email_input)

	_pass_input = UI.input("Password", true)
	col.add_child(_pass_input)

	_submit = UI.button("Log in")
	_submit.pressed.connect(_on_submit)
	col.add_child(_submit)

	_toggle = UI.button("Need an account? Register", false)
	_toggle.pressed.connect(_on_toggle)
	col.add_child(_toggle)

	_status = UI.label("", 14, UI.BAD)
	col.add_child(_status)

	col.add_child(UI.spacer(20))
	col.add_child(UI.label("Server", 13, UI.MUTED))
	_server_input = UI.input("https://your-domain.com/api")
	_server_input.text = Api.base_url
	_server_input.text_changed.connect(func(t): Api.set_base_url(t))
	col.add_child(_server_input)

	_apply_mode()

func _apply_mode() -> void:
	_name_input.visible = _register_mode
	_submit.text = "Create account" if _register_mode else "Log in"
	_toggle.text = "Have an account? Log in" if _register_mode else "Need an account? Register"
	_status.text = ""

func _on_toggle() -> void:
	_register_mode = not _register_mode
	_apply_mode()

func _set_busy(busy: bool) -> void:
	_submit.disabled = busy
	_submit.text = "Please wait..." if busy else ("Create account" if _register_mode else "Log in")

func _on_submit() -> void:
	Api.set_base_url(_server_input.text)
	var email := _email_input.text.strip_edges()
	var password := _pass_input.text
	if email == "" or password == "":
		_status.text = "Email and password are required."
		return

	_set_busy(true)
	var r: Dictionary
	if _register_mode:
		r = await Api.register(_name_input.text.strip_edges(), email, password)
	else:
		r = await Api.login(email, password)
	_set_busy(false)

	if r.get("ok", false):
		Session.go_to(GroupsScreen.new())
	else:
		_status.text = _friendly(r)

func _friendly(r: Dictionary) -> String:
	var msg: String = r.get("message", "")
	if r.get("error", "") == "no_connection":
		return "Can't reach the server. Check the Server URL below."
	return msg if msg != "" else "Something went wrong."

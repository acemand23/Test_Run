extends Node
## Global state: the logged-in user and the app-wide navigation entry point.
## Registered as an autoload singleton (see project.godot).

var user: Dictionary = {}            # {id, name, email}
var main: Node = null                # set by Main.gd so screens can navigate

func is_logged_in() -> bool:
	return not user.is_empty() and Api.token != ""

func set_user(u: Dictionary) -> void:
	user = u

func clear() -> void:
	user = {}
	Api.set_token("")

## Convenience navigation used by every screen.
func go_to(screen: Control) -> void:
	if main and main.has_method("show_screen"):
		main.show_screen(screen)

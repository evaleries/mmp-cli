<p align="center">
    <img title="MMP CLI" height="150" src="https://github.com/evaleries/mmp-cli/blob/master/assets/logo.png?raw=true" />
</p>

<h4> <center>This is an <bold>experimental project</bold>. </center></h4>

MMP CLI was created by, and is maintained by [evaleries](https://github.com/evaleries). This tool is a Moodle cli-based application that provides simple information directly taken from [MMP UNEJ](https://mmp.unej.ac.id/).

- Built on top of the [Laravel Zero](https://laravel-zero.com) components.
------

## Usage

### Setup
First of all, set up your NIM & Password in `.env` correctly.

Run `composer install` command.

Finally, try login by running `php mmp login`

### Commands

There are several available commands.

#### Authentication

You can login & fetching upcoming events directly from MMP by running `php mmp login`

Or logging-out by running `php mmp logout`. This command will delete the `cookies` and `responses` folder inside the storage.

#### Check Task / Assignments

You can easily check the upcoming assignments/tasks by runing these commands below.

- `php assign:list` - List of available tasks on current month.
- `php assign:list --latest` - Update the upcoming assignments.
- `php assign:list --custom` - Custom month.
- `php assign:detail` - Interactively see the detail of assignment.

#### Check Attendances

You can easily check the upcoming attendaces by running these commands below.

- `php mmp attend:list` - List of attendances on current month
- `php mmp attend:list --latest` - Update the upcoming attendances
- `php mmp attend:list --custom` - Custom month.


#### Help

If you need help with the command, pass `help` on the first argument before the command.

e.g: `php mmp help assign:list`

---

## Contribute

You can help this project by fixing an issue or make a new feature.
Feel free to open pull request on this repository.

## License

MMP CLI is an open-source software licensed under the [MIT license](https://github.com/evaleries/mmp-client/blob/master/LICENSE.md).

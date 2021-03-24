<p align="center">
    <img title="MMP CLI" height="150" src="https://github.com/evaleries/mmp-cli/blob/master/assets/logo.png?raw=true" />
</p>

<p align="center">
    <a href="https://github.styleci.io/repos/303184310?branch=master"><img src="https://github.styleci.io/repos/303184310/shield?branch=master" alt="StyleCI"></a>
    <a href="https://www.codacy.com/gh/evaleries/mmp-cli/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=evaleries/mmp-cli&amp;utm_campaign=Badge_Grade"><img src="https://img.shields.io/codacy/grade/48f2da43d1504cce89e37b7783af953b?style=flat-square"/></a>
</p>

<h4> <center>This is an <bold>experimental project</bold>. </center></h4>

MMP CLI was created by, and is maintained by [evaleries](https://github.com/evaleries). This tool is a Moodle cli-based application that provides simple information directly taken from [MMP UNEJ](https://mmp.unej.ac.id/).

-   Built on top of the [Laravel Zero](https://laravel-zero.com) components.

* * *

## Usage

### Setup

First of all, clone this repository.

Run `composer install` command.

Then, set up your NIM & Password in `.env` correctly.

Finally, try login by running `php mmp login`

### Commands

There are several available commands.

#### Authentication

You can login & fetching upcoming events directly from MMP by running `php mmp login`

Or logging-out by running `php mmp logout`. This command will delete the `cookies` and `responses` folder inside the storage.

#### Check Task / Assignments

You can easily check the upcoming assignments/tasks by running these commands below.

-   `php assign:list` - List of available tasks on current month.
-   `php assign:list --latest` - Update the upcoming assignments.
-   `php assign:list --custom` - Custom month.
-   `php assign:detail` - Interactively see the detail of assignment.

#### Check Attendances

You can easily check the upcoming attendaces by running these commands below.

-   `php mmp attend:list` - List of attendances on current month
-   `php mmp attend:list --latest` - Update the upcoming attendances
-   `php mmp attend:list --custom` - Custom month.
-   `php mmp attend:list --today` - List of today's attendance.
-   `php mmp attend:list --tomorrow` - List of tomorrow's attendance.
-   `php mmp attend:list --upcoming` - List of upcoming attendances in this month.
-   `php mmp attend:list --desc` - Reversed list of attendance (order desc by date).

#### Submit Attendance

Submit an attendance with ease.

-   `php mmp attend:submit` - Run command in interactive mode. (Give an options for course id & attendance status).
-   `php mmp attend:submit --course=COURSEID` - Submit attendance with specified _course id_ & set status to `Present`. This option won't give you an option (non interactive mode)
-   `php mmp attend:submit --course=COURSEID --status=Late` - You also can customize the attendance status to one of these `Present`, `Excused`, `Late`, `Absent`. The default is `Present`.
-   `php mmp attend:submit --all` - Show all attendances. Without passing `--all`, this command will show only for today's attendance.

#### Help

If you need help with the command, pass `help` on the first argument before the command.

e.g: `php mmp help assign:list`

* * *

## Contribute

You can help this project by fixing an issue or make a new feature.
Feel free to open pull request on this repository.

## License

MMP CLI is an open-source software licensed under the [MIT license](https://github.com/evaleries/mmp-cli/blob/master/LICENSE.md).

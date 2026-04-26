# Family Dashboard

This is a basic dashboard tool for families to use to help keep track of family events/birthdays, chores/to-dos, reminders, and other such things that pertain to family life.

## Features

- Parents can assign reminders/chores/to-dos.
- Everyone can create and manage events they create.
- Parents can edit events made by others.
- Kids can self-assign reminders/chores/to-dos.
- Everyone can manage their profile and parents can manage everyone else's profile.

**Possible Future Features**

- Add a family budget and kid expense tracking for teaching kids finances.
- Family meal prepping with shopping lists?
- Contact info section if using tool with extended family (could be helpful for keeping track of family group chats and birthdays)?
- Announcement section that can incorporate email messaging?
- Possible chat section (especially if doing a mobile app)
- If doing a mobile app, could incorporate location tracking (build in privacy and security focused permissions)
- Educational monitoring?
- Local weather
- Vacation planning?

## REST Endpoints

| Name | Method | Path | Middleware |
| ---- | ------ | ---- | ---------- |

## Database Design

**Events**

- ID
- User ID (who created/manages it)
- Event Name
- Event Location
- Start Date
- Start Time
- End Date
- End Time
- Description/Notes

**To Dos (Chores and Reminders)**

- ID
- User ID (who created it)
- Title
- Description/Notes
- User Assignment (who is assigned to do it)

**Users**

- ID
- Name
- Username
- Email
- Password
- Status (enum type of either child or parent)
- Birthdate
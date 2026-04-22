# Family Dashboard

This is a basic dashboard tool for families to use to help keep track of family events/birthdays, chores/to-dos, reminders, and other such things that pertain to family life.

## Features

## REST Endpoints

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
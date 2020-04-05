package com.example.databasephase3android;

import android.content.Intent;
import android.graphics.Typeface;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.TableLayout;
import android.widget.TableRow;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

public class MeetingPage extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_meeting);

        final Session session = new Session(MyApplication.getAppContext());
        int sid = session.getUserToEditID();
        int mid = session.getMeetingToViewID();

        TextView welcomeUser = findViewById (R.id.welcome);
        welcomeUser.setText("Welcome " + session.getLoggedInUserName() + "!");

        TextView meetingsPage = findViewById (R.id.meetingsPage);
        meetingsPage.setText("Meeting " + session.getMeetingToViewID() + ":");

        Button logoutBtn = findViewById(R.id.logout);
        logoutBtn.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                session.logoutUser();
                startActivity(new Intent(MeetingPage.this, LoginPage.class));
            }
        });

        Button backBtn = findViewById(R.id.back);
        backBtn.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                startActivity(new Intent(MeetingPage.this, HomeStudentPage.class));
            }
        });

        boolean isUserMenteeOfMeeting = UtilityClass.isUserMenteeOfMeeting(sid, mid);
        boolean isUserMentorOfMeeting = UtilityClass.isUserMentorOfMeeting(sid, mid);
        boolean isUserParentOfMenteeOfMeeting = UtilityClass.isUserParentOfMenteeOfMeeting(sid, mid);
        boolean isUserParentOfMentorOfMeeting = UtilityClass.isUserParentOfMentorOfMeeting(sid, mid);

        // display meeting information in meeting table
        TableLayout meetingTable = findViewById(R.id.meetingTable);

        TableRow tableName = new TableRow(this);
        TableRow.LayoutParams lp = new TableRow.LayoutParams(TableRow.LayoutParams.MATCH_PARENT);
        tableName.setLayoutParams(lp);

        TextView tableNameCol = new TextView(this);
        tableNameCol.setText("MEETING INFORMATION:");
        tableNameCol.setTypeface(null, Typeface.BOLD);

        tableName.addView(tableNameCol, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        meetingTable.addView(tableName, 0);

        TableRow headers = new TableRow(this);
        headers.setLayoutParams(lp);

        TextView id_col  = new TextView(this);
        id_col.setText("ID:");
        id_col.setTypeface(null, Typeface.BOLD);

        TextView name_col = new TextView(this);
        name_col.setText("NAME:");
        name_col.setTypeface(null, Typeface.BOLD);

        TextView date_col = new TextView(this);
        date_col.setText("DATE:");
        date_col.setTypeface(null, Typeface.BOLD);

        TextView time_slot_col = new TextView(this);
        time_slot_col.setText("TIME SLOT:");
        time_slot_col.setTypeface(null, Typeface.BOLD);

        TextView capacity_col  = new TextView(this);
        capacity_col.setText("CAPACITY:");
        capacity_col.setTypeface(null, Typeface.BOLD);

        TextView announcement_col = new TextView(this);
        announcement_col.setText("ANNOUNCEMENT:");
        announcement_col.setTypeface(null, Typeface.BOLD);

        headers.addView(id_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        headers.addView(name_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        headers.addView(date_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        headers.addView(time_slot_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        headers.addView(capacity_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        headers.addView(announcement_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        meetingTable.addView(headers, 1);

        JSONArray meetingInfoArray = UtilityClass.makePOST(String.format("SELECT * FROM meetings WHERE meet_id = %d", mid));
        int meeting_id = 0;
        String meeting_name = "";
        String meeting_date = "";
        int meeting_capacity = 0;
        String meeting_announcement = "";
        String meeting_time_slot_day = "";
        String meeting_time_slot_start = "";
        String meeting_time_slot_end = "";
        try {
            JSONObject meetingInfoObject = meetingInfoArray.getJSONObject(0);
            meeting_id = meetingInfoObject.getInt("meet_id");
            meeting_name = meetingInfoObject.getString("meet_name");
            meeting_date = meetingInfoObject.getString("date");
            int time_slot_id = meetingInfoObject.getInt("time_slot_id");
            meeting_capacity = meetingInfoObject.getInt("capacity");
            meeting_announcement = meetingInfoObject.getString("announcement");

            JSONArray time_slot_array = UtilityClass.makePOST(String.format("SELECT * FROM time_slot WHERE time_slot_id = %d LIMIT 1", time_slot_id));
            JSONObject time_slot_object = time_slot_array.getJSONObject(0);
            meeting_time_slot_day = time_slot_object.getString("day_of_the_week");
            meeting_time_slot_start = time_slot_object.getString("start_time");
            meeting_time_slot_end = time_slot_object.getString("end_time");
        } catch (JSONException e) {
            e.printStackTrace();
        }

        TableRow row = new TableRow(this);
        lp = new TableRow.LayoutParams(TableRow.LayoutParams.MATCH_PARENT);
        row.setLayoutParams(lp);

        TextView idView = new TextView(this);
        idView.setText(Integer.toString(meeting_id));

        TextView nameView = new TextView(this);
        nameView.setText(meeting_name);

        TextView dateView = new TextView(this);
        dateView.setText(meeting_date);

        TextView timeSlotView = new TextView(this);
        timeSlotView.setText(meeting_time_slot_day + " " + meeting_time_slot_start + " - " + meeting_time_slot_end);

        TextView capacityView = new TextView(this);
        capacityView.setText(Integer.toString(meeting_capacity));

        TextView announcementView = new TextView(this);
        announcementView.setText(meeting_announcement);

        row.addView(idView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        row.addView(nameView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        row.addView(dateView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        row.addView(timeSlotView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        row.addView(capacityView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        row.addView(announcementView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        meetingTable.addView(row, 2);

        //show material of the meeting if logged in as mentor, mentee, parent of mentee, parent of mentor, or admin
        TableLayout materialTable = findViewById(R.id.materialTable);
        if(session.isAdmin() || isUserMenteeOfMeeting || isUserMentorOfMeeting || isUserParentOfMenteeOfMeeting || isUserParentOfMentorOfMeeting) {

            TableRow materialTableName = new TableRow(this);
            TableRow.LayoutParams lp_material = new TableRow.LayoutParams(TableRow.LayoutParams.MATCH_PARENT);
            materialTableName.setLayoutParams(lp_material);

            TextView materialTableNameCol = new TextView(this);
            materialTableNameCol.setText("MEETING MATERIAL:");
            materialTableNameCol.setTypeface(null, Typeface.BOLD);

            materialTableName.addView(materialTableNameCol, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            materialTable.addView(materialTableName, 0);

            TableRow headers_material = new TableRow(this);
            headers_material.setLayoutParams(lp_material);

            TextView title_col  = new TextView(this);
            title_col.setText("TITLE:");
            title_col.setTypeface(null, Typeface.BOLD);

            TextView author_col = new TextView(this);
            author_col.setText("AUTHOR:");
            author_col.setTypeface(null, Typeface.BOLD);

            TextView type_col = new TextView(this);
            type_col.setText("TYPE:");
            type_col.setTypeface(null, Typeface.BOLD);

            TextView url_col = new TextView(this);
            url_col.setText("URL:");
            url_col.setTypeface(null, Typeface.BOLD);

            TextView assigned_date_col  = new TextView(this);
            assigned_date_col.setText("ASSIGNED DATE:");
            assigned_date_col.setTypeface(null, Typeface.BOLD);

            TextView notes_col = new TextView(this);
            notes_col.setText("NOTES:");
            notes_col.setTypeface(null, Typeface.BOLD);

            headers_material.addView(title_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            headers_material.addView(author_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            headers_material.addView(type_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            headers_material.addView(url_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            headers_material.addView(assigned_date_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            headers_material.addView(notes_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            materialTable.addView(headers_material, 1);

            JSONArray meetingMaterialArray = UtilityClass.makePOST(String.format("SELECT * FROM material WHERE material_id IN (SELECT material_id FROM assign WHERE meet_id = %d)", mid));
            for (int i = 2; i < meetingMaterialArray.length() + 2; i++) {
                String title = "";
                String author = "";
                String type = "";
                String url = "";
                String assigned_date = "";
                String notes = "";
                try {
                    JSONObject meetingMaterialObject = meetingMaterialArray.getJSONObject(i - 2);
                    title = meetingMaterialObject.getString("title");
                    author = meetingMaterialObject.getString("author");
                    type = meetingMaterialObject.getString("type");
                    url = meetingMaterialObject.getString("url");
                    assigned_date = meetingMaterialObject.getString("assigned_date");
                    notes = meetingMaterialObject.getString("notes");
                } catch (JSONException e) {
                    e.printStackTrace();
                }

                TableRow row_material = new TableRow(this);
                lp_material = new TableRow.LayoutParams(TableRow.LayoutParams.MATCH_PARENT);
                row_material.setLayoutParams(lp_material);

                TextView titleView = new TextView(this);
                titleView.setText(title);

                TextView authorView = new TextView(this);
                authorView.setText(author);

                TextView typeView = new TextView(this);
                typeView.setText(type);

                TextView urlView = new TextView(this);
                urlView.setText(url);

                TextView assignedDateView = new TextView(this);
                assignedDateView.setText(assigned_date);

                TextView notesView = new TextView(this);
                notesView.setText(notes);

                row_material.addView(titleView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
                row_material.addView(authorView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
                row_material.addView(typeView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
                row_material.addView(urlView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
                row_material.addView(assignedDateView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
                row_material.addView(notesView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
                materialTable.addView(row_material, i);
            }
        }
        else {
            materialTable.setVisibility(View.INVISIBLE);
        }

        //show mentors (name & email) if logged in as mentor or parent of mentor or admin
        TableLayout mentorsTable = findViewById(R.id.mentorsTable);
        if(session.isAdmin() || isUserMentorOfMeeting || isUserParentOfMentorOfMeeting) {
            TableRow mentorsTableName = new TableRow(this);
            TableRow.LayoutParams lp_mentors = new TableRow.LayoutParams(TableRow.LayoutParams.MATCH_PARENT);
            mentorsTableName.setLayoutParams(lp_mentors);

            TextView mentorsTableNameCol = new TextView(this);
            mentorsTableNameCol.setText("MENTORS:");
            mentorsTableNameCol.setTypeface(null, Typeface.BOLD);

            mentorsTableName.addView(mentorsTableNameCol, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            mentorsTable.addView(mentorsTableName, 0);

            TableRow headers_mentors = new TableRow(this);
            headers_mentors.setLayoutParams(lp_mentors);

            TextView mentor_name_col  = new TextView(this);
            mentor_name_col.setText("NAME:");
            mentor_name_col.setTypeface(null, Typeface.BOLD);

            TextView mentor_email_col = new TextView(this);
            mentor_email_col.setText("EMAIL:");
            mentor_email_col.setTypeface(null, Typeface.BOLD);

            headers_mentors.addView(mentor_name_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            headers_mentors.addView(mentor_email_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            mentorsTable.addView(headers_mentors, 1);

            JSONArray meetingMentorsArray = UtilityClass.makePOST(String.format("SELECT * FROM users INNER JOIN enroll2 ON enroll2.mentor_id = users.id WHERE meet_id = %d", mid));
            for (int i = 2; i < meetingMentorsArray.length() + 2; i++) {
                String mentor_name = "";
                String mentor_email = "";
                try {
                    JSONObject meetingMentorsObject = meetingMentorsArray.getJSONObject(i - 2);
                    mentor_name = meetingMentorsObject.getString("name");
                    mentor_email = meetingMentorsObject.getString("email");

                } catch (JSONException e) {
                    e.printStackTrace();
                }

                TableRow row_mentors = new TableRow(this);
                lp_mentors = new TableRow.LayoutParams(TableRow.LayoutParams.MATCH_PARENT);
                row_mentors.setLayoutParams(lp_mentors);

                TextView mentorNameView = new TextView(this);
                mentorNameView.setText(mentor_name);

                TextView mentorEmailView = new TextView(this);
                mentorEmailView.setText(mentor_email);

                row_mentors.addView(mentorNameView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
                row_mentors.addView(mentorEmailView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
                mentorsTable.addView(row_mentors, i);
            }
        }
        else {
            mentorsTable.setVisibility(View.INVISIBLE);
        }

        //show mentees (name & email) if logged in as mentor or parent of mentor or admin
        TableLayout menteesTable = findViewById(R.id.menteesTable);
        if(session.isAdmin() || isUserMentorOfMeeting || isUserParentOfMentorOfMeeting) {
            TableRow menteesTableName = new TableRow(this);
            TableRow.LayoutParams lp_mentees = new TableRow.LayoutParams(TableRow.LayoutParams.MATCH_PARENT);
            menteesTableName.setLayoutParams(lp_mentees);

            TextView menteesTableNameCol = new TextView(this);
            menteesTableNameCol.setText("MENTEES:");
            menteesTableNameCol.setTypeface(null, Typeface.BOLD);

            menteesTableName.addView(menteesTableNameCol, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            menteesTable.addView(menteesTableName, 0);

            TableRow headers_mentees = new TableRow(this);
            headers_mentees.setLayoutParams(lp_mentees);

            TextView mentee_name_col  = new TextView(this);
            mentee_name_col.setText("NAME:");
            mentee_name_col.setTypeface(null, Typeface.BOLD);

            TextView mentee_email_col = new TextView(this);
            mentee_email_col.setText("EMAIL:");
            mentee_email_col.setTypeface(null, Typeface.BOLD);

            headers_mentees.addView(mentee_name_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            headers_mentees.addView(mentee_email_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            menteesTable.addView(headers_mentees, 1);

            JSONArray meetingMenteesArray = UtilityClass.makePOST(String.format("SELECT * FROM users INNER JOIN enroll ON enroll.mentee_id = users.id WHERE meet_id = %d", mid));
            for (int i = 2; i < meetingMenteesArray.length() + 2; i++) {
                String mentee_name = "";
                String mentee_email = "";
                try {
                    JSONObject meetingMenteesObject = meetingMenteesArray.getJSONObject(i - 2);
                    mentee_name = meetingMenteesObject.getString("name");
                    mentee_email = meetingMenteesObject.getString("email");

                } catch (JSONException e) {
                    e.printStackTrace();
                }

                TableRow row_mentees = new TableRow(this);
                lp_mentees = new TableRow.LayoutParams(TableRow.LayoutParams.MATCH_PARENT);
                row_mentees.setLayoutParams(lp_mentees);

                TextView menteeNameView = new TextView(this);
                menteeNameView.setText(mentee_name);

                TextView menteeEmailView = new TextView(this);
                menteeEmailView.setText(mentee_email);

                row_mentees.addView(menteeNameView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
                row_mentees.addView(menteeEmailView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
                menteesTable.addView(row_mentees, i);
            }
        }
        else {
            menteesTable.setVisibility(View.INVISIBLE);
        }
    }
}

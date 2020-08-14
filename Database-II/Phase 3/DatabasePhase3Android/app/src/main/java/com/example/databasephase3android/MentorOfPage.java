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

public class MentorOfPage extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_mentor_of);

        final Session session = new Session(MyApplication.getAppContext());

        TextView welcomeUser = findViewById (R.id.welcome);
        welcomeUser.setText("Welcome " + session.getLoggedInUserName() + "!");

        TextView mentorsPage = findViewById (R.id.mentorsPage);
        mentorsPage.setText(session.getUserToEditName() + " is a mentor of:");

        Button logoutBtn = findViewById(R.id.logout);
        logoutBtn.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                session.logoutUser();
                startActivity(new Intent(MentorOfPage.this, LoginPage.class));
            }
        });

        Button backBtn = findViewById(R.id.back);
        backBtn.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                startActivity(new Intent(MentorOfPage.this, HomeStudentPage.class));
            }
        });

        // show all meetings student is mentoring
        TableLayout meetingsTable = findViewById(R.id.meetingsTable);

        TableRow tableName = new TableRow(this);
        TableRow.LayoutParams lp = new TableRow.LayoutParams(TableRow.LayoutParams.MATCH_PARENT);
        tableName.setLayoutParams(lp);

        TextView tableNameCol = new TextView(this);
        tableNameCol.setText("MEETINGS MENTOR OF:");
        tableNameCol.setTypeface(null, Typeface.BOLD);

        Button dropAllBtn = new Button(this);
        dropAllBtn.setText("Drop All Meetings");
        dropAllBtn.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                // query to drop all meetings
                UtilityClass.makePOST(String.format("DELETE FROM enroll2 WHERE mentor_id = %d", session.getUserToEditID()));
                UtilityClass.makePOST(String.format("DELETE FROM mentors WHERE mentor_id = %d", session.getUserToEditID()));
                startActivity(new Intent(MentorOfPage.this, MentorOfPage.class));
            }
        });

        tableName.addView(tableNameCol, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        tableName.addView(dropAllBtn, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        meetingsTable.addView(tableName, 0);

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

        TextView view_col  = new TextView(this);
        view_col.setText("VIEW MEETING:");
        view_col.setTypeface(null, Typeface.BOLD);

        TextView drop_col  = new TextView(this);
        drop_col.setText("DROP MEETING:");
        drop_col.setTypeface(null, Typeface.BOLD);

        TextView drop_future_col  = new TextView(this);
        drop_future_col.setText("DROP FUTURE MEETINGS:");
        drop_future_col.setTypeface(null, Typeface.BOLD);

        headers.addView(id_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        headers.addView(name_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        headers.addView(date_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        headers.addView(view_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        headers.addView(drop_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        headers.addView(drop_future_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        meetingsTable.addView(headers, 1);

        // get all meetings mentoring
        JSONArray getMeetingsArray = UtilityClass.makePOST(String.format("SELECT meetings.meet_id, meet_name, date FROM meetings INNER JOIN enroll2 ON enroll2.meet_id = meetings.meet_id WHERE mentor_id = %d", session.getUserToEditID()));

        for (int i = 2; i < getMeetingsArray.length() + 2; i++) {
            int id = 0;
            String name = "";
            String date = "";
            try {
                JSONObject getMeetingsObject = getMeetingsArray.getJSONObject(i - 2);
                id = getMeetingsObject.getInt("meet_id");
                name = getMeetingsObject.getString("meet_name");
                date = getMeetingsObject.getString("date");
            } catch (JSONException e) {
                e.printStackTrace();
            }

            TableRow row = new TableRow(this);
            lp = new TableRow.LayoutParams(TableRow.LayoutParams.MATCH_PARENT);
            row.setLayoutParams(lp);

            TextView idView = new TextView(this);
            idView.setText(Integer.toString(id));

            TextView nameView = new TextView(this);
            nameView.setText(name);

            TextView dateView = new TextView(this);
            dateView.setText(date);

            Button viewBtn = new Button(this);
            viewBtn.setText("View");
            viewBtn.setTag(R.string.id_tag, id);
            viewBtn.setOnClickListener(new View.OnClickListener() {

                @Override
                public void onClick(View v) {
                    session.setMeetingToViewID((int)v.getTag(R.string.id_tag));
                    startActivity(new Intent(MentorOfPage.this, MeetingPage.class));
                }
            });

            Button dropBtn = new Button(this);
            dropBtn.setText("Drop");
            dropBtn.setTag(R.string.id_tag, id);
            dropBtn.setOnClickListener(new View.OnClickListener() {

                @Override
                public void onClick(View v) {
                    int mid = (int)v.getTag(R.string.id_tag);
                    // query to drop a meeting
                    UtilityClass.makePOST(String.format("DELETE FROM enroll2 WHERE mentor_id = %d AND meet_id = %d", session.getUserToEditID(), mid));
                    // check to see if still present in enroll2 table, if so keep in mentors, otherwise delete
                    JSONArray is_still_mentor_array = UtilityClass.makePOST(String.format("SELECT * FROM enroll2 WHERE mentor_id = %d LIMIT 1", session.getUserToEditID()));
                    if (is_still_mentor_array == null || is_still_mentor_array.length() <= 0) {
                        UtilityClass.makePOST(String.format("DELETE FROM mentors WHERE mentor_id = %d", session.getUserToEditID()));
                    }
                    startActivity(new Intent(MentorOfPage.this, MentorOfPage.class));
                }
            });

            Button dropFutureBtn = new Button(this);
            dropFutureBtn.setText("Drop Future");
            dropFutureBtn.setTag(R.string.id_tag, id);
            dropFutureBtn.setOnClickListener(new View.OnClickListener() {

                @Override
                public void onClick(View v) {
                    int mid = (int)v.getTag(R.string.id_tag);
                    // queries to drop future meetings (meetings with same name)
                    JSONArray meetings_with_same_name_array = UtilityClass.makePOST(String.format("SELECT meet_id FROM meetings WHERE group_id = (SELECT group_id FROM meetings WHERE meet_id = %d LIMIT 1) AND meet_name = (SELECT meet_name FROM meetings WHERE meet_id = %d)", mid, mid));
                    for (int j = 0; j < meetings_with_same_name_array.length(); j++) {
                        int new_mid = 0;
                        try {
                            JSONObject meetings_with_same_name_object = meetings_with_same_name_array.getJSONObject(j);
                            new_mid = meetings_with_same_name_object.getInt("meet_id");
                        } catch (JSONException e) {
                            e.printStackTrace();
                        }
                        UtilityClass.makePOST(String.format("DELETE FROM enroll2 WHERE mentor_id = %d AND meet_id = %d", session.getUserToEditID(), new_mid));
                    }
                    // check to see if still present in enroll2 table, if so keep in mentors, otherwise delete
                    JSONArray is_still_mentor_array = UtilityClass.makePOST(String.format("SELECT * FROM enroll2 WHERE mentor_id = %d LIMIT 1", session.getUserToEditID()));
                    if (is_still_mentor_array == null || is_still_mentor_array.length() <= 0) {
                        UtilityClass.makePOST(String.format("DELETE FROM mentors WHERE mentor_id = %d", session.getUserToEditID()));
                    }
                    startActivity(new Intent(MentorOfPage.this, MentorOfPage.class));
                }
            });

            row.addView(idView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            row.addView(nameView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            row.addView(dateView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            row.addView(viewBtn, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            row.addView(dropBtn, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            row.addView(dropFutureBtn, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            meetingsTable.addView(row,i);
        }
    }
}

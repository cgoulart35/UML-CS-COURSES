package com.example.databasephase3android;

import android.content.Intent;
import android.graphics.Typeface;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.TableLayout;
import android.widget.TableRow;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.time.DayOfWeek;
import java.time.LocalDate;
import java.time.ZoneId;
import java.time.temporal.TemporalAdjusters;
import java.util.Calendar;
import java.util.Date;

public class PossibleMentorOfPage extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_possible_mentor_of);

        final Session session = new Session(MyApplication.getAppContext());

        TextView welcomeUser = findViewById (R.id.welcome);
        welcomeUser.setText("Welcome " + session.getLoggedInUserName() + "!");

        TextView possibleMentorsPage = findViewById (R.id.possibleMentorsPage);
        possibleMentorsPage.setText(session.getUserToEditName() + " can mentor:");

        Button logoutBtn = findViewById(R.id.logout);
        logoutBtn.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                session.logoutUser();
                startActivity(new Intent(PossibleMentorOfPage.this, LoginPage.class));
            }
        });

        Button backBtn = findViewById(R.id.back);
        backBtn.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                startActivity(new Intent(PossibleMentorOfPage.this, HomeStudentPage.class));
            }
        });

        // show all possible meetings student can mentor
        TableLayout meetingsTable = findViewById(R.id.meetingsTable);

        TableRow tableName = new TableRow(this);
        TableRow.LayoutParams lp = new TableRow.LayoutParams(TableRow.LayoutParams.MATCH_PARENT);
        tableName.setLayoutParams(lp);

        TextView tableNameCol = new TextView(this);
        tableNameCol.setText("POSSIBLE MEETINGS MENTOR OF:");
        tableNameCol.setTypeface(null, Typeface.BOLD);

        tableName.addView(tableNameCol, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
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

        TextView add_col  = new TextView(this);
        add_col.setText("ADD MEETING:");
        add_col.setTypeface(null, Typeface.BOLD);

        TextView add_future_col  = new TextView(this);
        add_future_col.setText("ADD FUTURE MEETINGS:");
        add_future_col.setTypeface(null, Typeface.BOLD);

        headers.addView(id_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        headers.addView(name_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        headers.addView(date_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        headers.addView(view_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        headers.addView(add_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        headers.addView(add_future_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        meetingsTable.addView(headers, 1);

        // show only possible meetings that are in the future and this weekend by Thursday
        Calendar calendar = Calendar.getInstance();
        int currentDayOfWeek = calendar.get(Calendar.DAY_OF_WEEK);
        SimpleDateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd");
        Date currentDate = calendar.getTime();
        LocalDate currentDateLocal = LocalDate.parse(dateFormat.format(currentDate));
        //Log.d("currentDayOfWeek", Integer.toString(currentDayOfWeek));
        //Log.d("currentDate", dateFormat.format(currentDate));

        // if date is before this weeks thursday; show meetings with dates this saturday and on
        String append_future_dates_query = "";
        if (currentDayOfWeek == 2 || currentDayOfWeek == 3 || currentDayOfWeek == 4 || currentDayOfWeek == 7 || currentDayOfWeek == 1) {
            LocalDate thisSaturdayDateLocal = currentDateLocal.with(TemporalAdjusters.next(DayOfWeek.SATURDAY));
            Date thisSaturdayDate = Date.from(thisSaturdayDateLocal.atStartOfDay(ZoneId.systemDefault()).toInstant());
            append_future_dates_query = String.format("AND date >= '%s'", dateFormat.format(thisSaturdayDate));
            //Log.d("append_future_dates_query --- thisSaturdayDate", append_future_dates_query);
        }
        // if date is after or is this thursday; show meetings with dates next saturday and on
        else {
            LocalDate thisSaturdayDateLocal = currentDateLocal.with(TemporalAdjusters.next(DayOfWeek.SATURDAY));
            LocalDate nextSaturdayDateLocal = thisSaturdayDateLocal.plusWeeks(1);

            Date nextSaturdayDate = Date.from(nextSaturdayDateLocal.atStartOfDay(ZoneId.systemDefault()).toInstant());
            append_future_dates_query = String.format("AND date >= '%s'", dateFormat.format(nextSaturdayDate));
            //Log.d("append_future_dates_query --- nextSaturdayDate", append_future_dates_query);
        }

        JSONArray getUserGroupArray = UtilityClass.makePOST(String.format("SELECT * FROM groups WHERE description = (SELECT grade FROM students WHERE student_id = %d LIMIT 1) LIMIT 1", session.getUserToEditID()));
        int mentee_grade_req = 0;
        try {
            JSONObject getUserGroupObject = getUserGroupArray.getJSONObject(0);
            mentee_grade_req = getUserGroupObject.getInt("mentee_grade_req");
        } catch (JSONException e) {
            e.printStackTrace();
        }

        //possible meetings are all meetings in the future (also meetings this weekend if its before Thursday) where we are not already a mentor for that weekend (specifically that date, and that date + 1 if it's Saturday, and that date - 1 if it's Sunday; we use both date + 1 and date - 1 because meetings are not on Fridays or Mondays

        // get all possible meetings user can mentor
        JSONArray getMeetingsArray = UtilityClass.makePOST(String.format("SELECT * FROM meetings WHERE group_id IN (SELECT group_id FROM groups WHERE description <= %d) AND meet_id NOT IN (SELECT meet_id FROM enroll2 WHERE mentor_id = %d) AND date NOT IN ((SELECT date FROM meetings INNER JOIN enroll2 ON meetings.meet_id = enroll2.meet_id WHERE mentor_id = %d) UNION (SELECT date - 1 FROM meetings INNER JOIN enroll2 ON meetings.meet_id = enroll2.meet_id WHERE mentor_id = %d) UNION (SELECT date + 1 FROM meetings INNER JOIN enroll2 ON meetings.meet_id = enroll2.meet_id WHERE mentor_id = %d)) ", mentee_grade_req, session.getUserToEditID(), session.getUserToEditID(), session.getUserToEditID(), session.getUserToEditID()) + append_future_dates_query);

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
                    startActivity(new Intent(PossibleMentorOfPage.this, MeetingPage.class));
                }
            });

            Button addBtn = new Button(this);
            addBtn.setText("Add");
            addBtn.setTag(R.string.id_tag, id);
            addBtn.setOnClickListener(new View.OnClickListener() {

                @Override
                public void onClick(View v) {
                    int mid = (int)v.getTag(R.string.id_tag);

                    // get count of how many mentors are already enrolled
                    JSONArray check_mentor_count_array = UtilityClass.makePOST(String.format("SELECT count(mentor_id) FROM enroll2 WHERE meet_id = %d LIMIT 1", mid));
                    int mentor_count = 0;
                    try {
                        JSONObject check_mentor_count_object = check_mentor_count_array.getJSONObject(0);
                        mentor_count = check_mentor_count_object.getInt("count(mentor_id)");
                    } catch (JSONException e) {
                        e.printStackTrace();
                    }

                    // if the amount of mentors < 3, add; otherwise throw toast error
                    if (mentor_count < 3) {
                        UtilityClass.makePOST(String.format("INSERT INTO mentors (mentor_id) VALUES (%d)", session.getUserToEditID()));
                        UtilityClass.makePOST(String.format("INSERT INTO enroll2 (meet_id, mentor_id) VALUES (%d, %d)", mid, session.getUserToEditID()));
                        startActivity(new Intent(PossibleMentorOfPage.this, PossibleMentorOfPage.class));
                    }
                    else {
                        Toast cannotAddError = Toast.makeText(PossibleMentorOfPage.this, "Error: Already 3 mentors.", Toast.LENGTH_SHORT);
                        cannotAddError.show();
                    }
                }
            });

            Button addFutureBtn = new Button(this);
            addFutureBtn.setText("Add Future");
            addFutureBtn.setTag(R.string.id_tag, id);
            addFutureBtn.setOnClickListener(new View.OnClickListener() {

                @Override
                public void onClick(View v) {
                    int mid = (int)v.getTag(R.string.id_tag);

                    // get count of how many mentors are already enrolled
                    JSONArray check_mentor_count_array = UtilityClass.makePOST(String.format("SELECT count(mentor_id) FROM enroll2 WHERE meet_id = %d LIMIT 1", mid));
                    int mentor_count = 0;
                    try {
                        JSONObject check_mentor_count_object = check_mentor_count_array.getJSONObject(0);
                        mentor_count = check_mentor_count_object.getInt("count(mentor_id)");
                    } catch (JSONException e) {
                        e.printStackTrace();
                    }

                    // if the amount of mentors < 3, add; otherwise throw toast error
                    if (mentor_count < 3) {
                        UtilityClass.makePOST(String.format("INSERT INTO mentors (mentor_id) VALUES (%d)", session.getUserToEditID()));
                        UtilityClass.makePOST(String.format("INSERT INTO enroll2 (meet_id, mentor_id) VALUES (%d, %d)", mid, session.getUserToEditID()));

                        // add other meetings with the same name as well (that don't have 3 mentors); we don't use meeting id because every meeting has a different id even if its the same meeting/section that is meeting 2 weeks/etc. later; we also don't use group_id because group_id is the id of the grade level and we don't want to add all sections of all meetings in that grade at once, only the same sections of meetings
                        boolean couldntAddMeeting = false;
                        JSONArray meetings_with_same_name_array = UtilityClass.makePOST(String.format("SELECT meet_id FROM meetings WHERE group_id = (SELECT group_id FROM meetings WHERE meet_id = %d LIMIT 1) AND meet_name = (SELECT meet_name FROM meetings WHERE meet_id = %d) AND date NOT IN ((SELECT date FROM meetings  WHERE meet_id = %d) UNION (SELECT date - 1 FROM meetings  WHERE meet_id = %d) UNION (SELECT date + 1 FROM meetings WHERE meet_id = %d))", mid, mid, mid, mid, mid));
                        for (int k = 0; k < meetings_with_same_name_array.length(); k++) {
                            int new_mid = 0;
                            try {
                                JSONObject meeting_with_same_name_object = meetings_with_same_name_array.getJSONObject(k);
                                new_mid = meeting_with_same_name_object.getInt("meet_id");

                                // get count of how many mentors are already enrolled
                                JSONArray check_new_mentor_count_array = UtilityClass.makePOST(String.format("SELECT count(mentor_id) FROM enroll2 WHERE meet_id = %d LIMIT 1", new_mid));
                                JSONObject check_new_mentor_count_object = check_new_mentor_count_array.getJSONObject(0);
                                int new_mentor_count = check_new_mentor_count_object.getInt("count(mentor_id)");

                                if (new_mentor_count < 3) {
                                    // add meeting with same name
                                    UtilityClass.makePOST(String.format("INSERT INTO enroll2 (meet_id, mentor_id) VALUES (%d, %d)", new_mid, session.getUserToEditID()));
                                }
                                else {
                                    couldntAddMeeting = true;
                                }
                            } catch (JSONException e) {
                                e.printStackTrace();
                            }
                        }

                        startActivity(new Intent(PossibleMentorOfPage.this, PossibleMentorOfPage.class));
                        if (couldntAddMeeting) {
                            Toast couldntAddError = Toast.makeText(PossibleMentorOfPage.this, "Error: Some future meeting(s) already have 3 mentors.", Toast.LENGTH_SHORT);
                            couldntAddError.show();
                        }
                    }
                    else {
                        Toast cannotAddError = Toast.makeText(PossibleMentorOfPage.this, "Error: Already 3 mentors.", Toast.LENGTH_SHORT);
                        cannotAddError.show();
                    }
                }
            });

            row.addView(idView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            row.addView(nameView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            row.addView(dateView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            row.addView(viewBtn, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            row.addView(addBtn, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            row.addView(addFutureBtn, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            meetingsTable.addView(row,i);
        }
    }
}

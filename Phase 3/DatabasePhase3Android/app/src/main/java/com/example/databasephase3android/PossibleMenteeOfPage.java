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

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.time.DayOfWeek;
import java.time.LocalDate;
import java.time.ZoneId;
import java.time.temporal.TemporalAdjusters;
import java.util.Calendar;
import java.util.Date;

public class PossibleMenteeOfPage extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_possible_mentee_of);

        final Session session = new Session(MyApplication.getAppContext());

        TextView welcomeUser = findViewById (R.id.welcome);
        welcomeUser.setText("Welcome " + session.getLoggedInUserName() + "!");

        TextView possibleMenteesPage = findViewById (R.id.possibleMenteesPage);
        possibleMenteesPage.setText(session.getUserToEditName() + " can mentee:");

        Button logoutBtn = findViewById(R.id.logout);
        logoutBtn.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                session.logoutUser();
                startActivity(new Intent(PossibleMenteeOfPage.this, LoginPage.class));
            }
        });

        Button backBtn = findViewById(R.id.back);
        backBtn.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                startActivity(new Intent(PossibleMenteeOfPage.this, HomeStudentPage.class));
            }
        });

        // show all possible meetings student can mentee
        TableLayout meetingsTable = findViewById(R.id.meetingsTable);

        TableRow tableName = new TableRow(this);
        TableRow.LayoutParams lp = new TableRow.LayoutParams(TableRow.LayoutParams.MATCH_PARENT);
        tableName.setLayoutParams(lp);

        TextView tableNameCol = new TextView(this);
        tableNameCol.setText("POSSIBLE MEETINGS MENTEE OF:");
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
        final Calendar calendar = Calendar.getInstance();
        int currentDayOfWeek = calendar.get(Calendar.DAY_OF_WEEK);
        final SimpleDateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd");
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
        int mentor_grade_req = 0;
        int group_id = 0;
        try {
            JSONObject getUserGroupObject = getUserGroupArray.getJSONObject(0);
            mentor_grade_req = getUserGroupObject.getInt("mentor_grade_req");
            group_id = getUserGroupObject.getInt("group_id");
        } catch (JSONException e) {
            e.printStackTrace();
        }

        //possible meetings are meetings that are not on the same date and time-slot as enrolled meetings, and are math classes a certain weekend if not yet enrolled in a math class that weekend, and english classes a weekend if not yet enrolled in an english class that weekend

        // get all possible meetings user can mentee
        JSONArray getMeetingsArray = UtilityClass.makePOST(String.format("SELECT * FROM meetings WHERE group_id = %d AND meet_id NOT IN (SELECT meet_id FROM enroll WHERE mentee_id = %d) AND (time_slot_id, date) NOT IN (SELECT time_slot_id, date FROM meetings INNER JOIN enroll ON meetings.meet_id = enroll.meet_id WHERE mentee_id = %d)", group_id, session.getUserToEditID(), session.getUserToEditID()) + append_future_dates_query);

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

            // meetings are also only possible for mentees if the meeting is a type of class we aren't enrolled in that weekend
            // get weekend dates (saturday and sunday) of that meeting
            Date meet_date = null;
            try {
                meet_date = dateFormat.parse(date);
            } catch (ParseException e) {
                e.printStackTrace();
            }
            calendar.setTime(meet_date);
            String sat_date = "";
            String sun_date = "";
            if (calendar.get(Calendar.DAY_OF_WEEK) == Calendar.SATURDAY) {
                sat_date = date;
                calendar.add(Calendar.DATE, 1);
                sun_date = dateFormat.format(calendar.getTime());
            }
            else if (calendar.get(Calendar.DAY_OF_WEEK) == Calendar.SUNDAY) {
                sun_date = date;
                calendar.add(Calendar.DATE, -1);
                sat_date = dateFormat.format(calendar.getTime());
            }

            // figure out types of classes enrolled in that weekend
            JSONArray types_of_classes_enrolled_in_that_weekend_query = UtilityClass.makePOST(String.format("SELECT meet_name FROM meetings INNER JOIN enroll ON meetings.meet_id = enroll.meet_id WHERE mentee_id = %d AND date <= '%s' AND date >= '%s'", session.getUserToEditID(), sun_date, sat_date));

            //figure out if this meeting is in types of classes we are enrolled in
            boolean enrolled_in_type_already_that_weekend = false;
            for (int x = 0; x < types_of_classes_enrolled_in_that_weekend_query.length(); x++) {
                try {
                    JSONObject class_type_object = types_of_classes_enrolled_in_that_weekend_query.getJSONObject(x);
                    String class_type = class_type_object.getString("meet_name");
                    if (name == class_type) {
                        enrolled_in_type_already_that_weekend = true;
                    }
                } catch (JSONException e) {
                    e.printStackTrace();
                }
            }

            if (!enrolled_in_type_already_that_weekend) {

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
                        session.setMeetingToViewID((int) v.getTag(R.string.id_tag));
                        startActivity(new Intent(PossibleMenteeOfPage.this, MeetingPage.class));
                    }
                });

                Button addBtn = new Button(this);
                addBtn.setText("Add");
                addBtn.setTag(R.string.id_tag, id);
                addBtn.setOnClickListener(new View.OnClickListener() {

                    @Override
                    public void onClick(View v) {
                        int mid = (int) v.getTag(R.string.id_tag);

                        // get count of how many mentees are already enrolled
                        JSONArray check_mentee_count_array = UtilityClass.makePOST(String.format("SELECT count(mentee_id) FROM enroll WHERE meet_id = %d LIMIT 1", mid));
                        int mentee_count = 0;
                        try {
                            JSONObject check_mentee_count_object = check_mentee_count_array.getJSONObject(0);
                            mentee_count = check_mentee_count_object.getInt("count(mentee_id)");
                        } catch (JSONException e) {
                            e.printStackTrace();
                        }

                        // if the amount of mentees < 6, add; otherwise throw toast error
                        if (mentee_count < 6) {
                            UtilityClass.makePOST(String.format("INSERT INTO mentees (mentee_id) VALUES (%d)", session.getUserToEditID()));
                            UtilityClass.makePOST(String.format("INSERT INTO enroll (meet_id, mentee_id) VALUES (%d, %d)", mid, session.getUserToEditID()));
                            startActivity(new Intent(PossibleMenteeOfPage.this, PossibleMenteeOfPage.class));
                        }
                        else {
                            Toast cannotAddError = Toast.makeText(PossibleMenteeOfPage.this, "Error: Already 6 mentees.", Toast.LENGTH_SHORT);
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
                        int mid = (int) v.getTag(R.string.id_tag);

                        // get count of how many mentees are already enrolled
                        JSONArray check_mentee_count_array = UtilityClass.makePOST(String.format("SELECT count(mentee_id) FROM enroll WHERE meet_id = %d LIMIT 1", mid));
                        int mentee_count = 0;
                        try {
                            JSONObject check_mentee_count_object = check_mentee_count_array.getJSONObject(0);
                            mentee_count = check_mentee_count_object.getInt("count(mentee_id)");
                        } catch (JSONException e) {
                            e.printStackTrace();
                        }

                        // if the amount of mentees < 6, add; otherwise throw toast error
                        if (mentee_count < 6) {
                            UtilityClass.makePOST(String.format("INSERT INTO mentees (mentee_id) VALUES (%d)", session.getUserToEditID()));
                            UtilityClass.makePOST(String.format("INSERT INTO enroll (meet_id, mentee_id) VALUES (%d, %d)", mid, session.getUserToEditID()));

                            // add other meetings with the same name as well (that don't have 6 mentees); we don't use meeting id because every meeting has a different id even if its the same meeting/section that is meeting 2 weeks/etc. later; we also don't use group_id because group_id is the id of the grade level and we don't want to add all sections of all meetings in that grade at once, only the same sections of meetings
                            boolean couldntAddMeetingCount = false;
                            boolean couldntAddMeetingType = false;
                            JSONArray meetings_with_same_name_array = UtilityClass.makePOST(String.format("SELECT * FROM meetings WHERE group_id = (SELECT group_id FROM meetings WHERE meet_id = %d LIMIT 1) AND meet_name = (SELECT meet_name FROM meetings WHERE meet_id = %d)", mid, mid));
                            for (int k = 0; k < meetings_with_same_name_array.length(); k++) {
                                int new_mid = 0;
                                String new_name = "";
                                String new_date = "";
                                try {
                                    JSONObject meeting_with_same_name_object = meetings_with_same_name_array.getJSONObject(k);
                                    new_mid = meeting_with_same_name_object.getInt("meet_id");
                                    new_name = meeting_with_same_name_object.getString("meet_name");
                                    new_date = meeting_with_same_name_object.getString("meet_date");

                                    // get count of how many mentees are already enrolled
                                    JSONArray check_new_mentee_count_array = UtilityClass.makePOST(String.format("SELECT count(mentee_id) FROM enroll WHERE meet_id = %d LIMIT 1", new_mid));
                                    JSONObject check_new_mentee_count_object = check_new_mentee_count_array.getJSONObject(0);
                                    int new_mentee_count = check_new_mentee_count_object.getInt("count(mentee_id)");

                                    if (new_mentee_count < 6) {

                                        // meetings are also only possible for mentees if the meeting is a type of class we aren't enrolled in that weekend
                                        // get weekend dates (saturday and sunday) of that meeting
                                        Date new_meet_date = null;
                                        try {
                                            new_meet_date = dateFormat.parse(new_date);
                                        } catch (ParseException e) {
                                            e.printStackTrace();
                                        }
                                        calendar.setTime(new_meet_date);
                                        String new_sat_date = "";
                                        String new_sun_date = "";
                                        if (calendar.get(Calendar.DAY_OF_WEEK) == Calendar.SATURDAY) {
                                            new_sat_date = new_date;
                                            calendar.add(Calendar.DATE, 1);
                                            new_sun_date = dateFormat.format(calendar.getTime());
                                        }
                                        else if (calendar.get(Calendar.DAY_OF_WEEK) == Calendar.SUNDAY) {
                                            new_sun_date = new_date;
                                            calendar.add(Calendar.DATE, -1);
                                            new_sat_date = dateFormat.format(calendar.getTime());
                                        }

                                        // figure out types of classes enrolled in that weekend
                                        JSONArray types_of_classes_enrolled_in_that_weekend_query = UtilityClass.makePOST(String.format("SELECT meet_name FROM meetings INNER JOIN enroll ON meetings.meet_id = enroll.meet_id WHERE mentee_id = %d AND date <= '%s' AND date >= '%s'", session.getUserToEditID(), new_sun_date, new_sat_date));

                                        //figure out if this meeting is in types of classes we are enrolled in
                                        boolean enrolled_in_type_already_that_weekend_new = false;
                                        for (int x = 0; x < types_of_classes_enrolled_in_that_weekend_query.length(); x++) {
                                            try {
                                                JSONObject class_type_object = types_of_classes_enrolled_in_that_weekend_query.getJSONObject(x);
                                                String class_type = class_type_object.getString("meet_name");
                                                if (new_name == class_type) {
                                                    enrolled_in_type_already_that_weekend_new = true;
                                                }
                                            } catch (JSONException e) {
                                                e.printStackTrace();
                                            }
                                        }

                                        if (!enrolled_in_type_already_that_weekend_new) {

                                            // add meeting with same name
                                            UtilityClass.makePOST(String.format("INSERT INTO enroll (meet_id, mentee_id) VALUES (%d, %d)", new_mid, session.getUserToEditID()));

                                        }
                                        else {
                                            couldntAddMeetingType = true;
                                        }
                                    }
                                    else {
                                        couldntAddMeetingCount = true;
                                    }
                                } catch (JSONException e) {
                                    e.printStackTrace();
                                }
                            }

                            startActivity(new Intent(PossibleMenteeOfPage.this, PossibleMenteeOfPage.class));
                            if (couldntAddMeetingCount || couldntAddMeetingType) {
                                String errorMsg = "Error: ";
                                if (couldntAddMeetingCount) {
                                    errorMsg += "Some future meeting(s) already have 6 mentees. ";
                                }
                                if (couldntAddMeetingType) {
                                    errorMsg += "Some future meeting(s) weren't added since some you are already enrolled in that type of class that weekend.";
                                }
                                Toast couldntAddError = Toast.makeText(PossibleMenteeOfPage.this, errorMsg, Toast.LENGTH_SHORT);
                                couldntAddError.show();
                            }
                        }
                        else {
                            Toast cannotAddError = Toast.makeText(PossibleMenteeOfPage.this, "Error: Already 6 mentees.", Toast.LENGTH_SHORT);
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
                meetingsTable.addView(row, i);
            }
        }
    }
}

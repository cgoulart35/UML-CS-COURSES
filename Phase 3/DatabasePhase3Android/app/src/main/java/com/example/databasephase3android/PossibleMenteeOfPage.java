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

        // get all possible meetings user can mentee
        //TODO
        JSONArray getMeetingsArray = UtilityClass.makePOST(String.format(""));

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
                    startActivity(new Intent(PossibleMenteeOfPage.this, MeetingPage.class));
                }
            });

            Button addBtn = new Button(this);
            addBtn.setText("Add");
            addBtn.setTag(R.string.id_tag, id);
            addBtn.setOnClickListener(new View.OnClickListener() {

                @Override
                public void onClick(View v) {
                    int mid = (int)v.getTag(R.string.id_tag);
                    //TODO query to add meeting
                    startActivity(new Intent(PossibleMenteeOfPage.this, PossibleMenteeOfPage.class));
                }
            });

            Button addFutureBtn = new Button(this);
            addFutureBtn.setText("Add Future");
            addFutureBtn.setTag(R.string.id_tag, id);
            addFutureBtn.setOnClickListener(new View.OnClickListener() {

                @Override
                public void onClick(View v) {
                    int mid = (int)v.getTag(R.string.id_tag);
                    //TODO query to add future
                    startActivity(new Intent(PossibleMenteeOfPage.this, PossibleMenteeOfPage.class));
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

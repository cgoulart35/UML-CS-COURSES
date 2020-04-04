package com.example.databasephase3android;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

public class HomeStudentPage extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_home_student);

        final Session session = new Session(MyApplication.getAppContext());

        TextView welcomeUser = findViewById (R.id.welcome);
        welcomeUser.setText("Welcome " + session.getLoggedInUserName() + "!");

        TextView usersPage = findViewById (R.id.usersPage);
        usersPage.setText(session.getUserToEditName() + "'s Student Page:");

        Button homeBtn = findViewById(R.id.home);
        if (session.isParent() || session.isAdmin())
            homeBtn.setOnClickListener(new View.OnClickListener() {

                @Override
                public void onClick(View v) {
                    session.setUserToEdit(session.getLoggedInUserID(), session.getLoggedInUserName());
                    if (session.isParent()) {
                        startActivity(new Intent(HomeStudentPage.this, HomeParentPage.class));
                    }
                    else if (session.isAdmin()) {
                        startActivity(new Intent(HomeStudentPage.this, HomeAdminPage.class));
                    }
                }
            });
        else {
            homeBtn.setVisibility(View.INVISIBLE);
        }

        Button logoutBtn = findViewById(R.id.logout);
        logoutBtn.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                session.logoutUser();
                startActivity(new Intent(HomeStudentPage.this, LoginPage.class));
            }
        });

        Button updateAccount = findViewById(R.id.update);
        updateAccount.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                startActivity(new Intent(HomeStudentPage.this, UpdateAccount.class));
            }
        });

        // check to see if student can be a mentor, and if so display buttons
        JSONArray getUserGroupArray = UtilityClass.makePOST(String.format("SELECT * FROM groups WHERE description = (SELECT grade FROM students WHERE student_id = %d LIMIT 1) LIMIT 1", session.getUserToEditID()));
        boolean mentor_grade_req = true;
        boolean mentee_grade_req = true;;
        try {
            JSONObject getUserGroupObject = getUserGroupArray.getJSONObject(0);
            mentor_grade_req = getUserGroupObject.isNull("mentor_grade_req");
            mentee_grade_req = getUserGroupObject.isNull("mentee_grade_req");
        } catch (JSONException e) {
            e.printStackTrace();
        }

        Button mentorOf = findViewById(R.id.mentorOf);
        Button possibleMentorOf = findViewById(R.id.possibleMentorOf);
        if (!mentee_grade_req) {
            mentorOf.setOnClickListener(new View.OnClickListener() {

                @Override
                public void onClick(View v) {
                    startActivity(new Intent(HomeStudentPage.this, MentorOfPage.class));
                }
            });

            possibleMentorOf.setOnClickListener(new View.OnClickListener() {

                @Override
                public void onClick(View v) {
                    startActivity(new Intent(HomeStudentPage.this, PossibleMentorOfPage.class));
                }
            });
        }
        else {
            mentorOf.setVisibility(View.INVISIBLE);
            possibleMentorOf.setVisibility(View.INVISIBLE);
        }

        // check to see if student can be a mentee, and if so display buttons
        Button menteeOf = findViewById(R.id.menteeOf);
        Button possibleMenteeOf = findViewById(R.id.possibleMenteeOf);
        if (!mentor_grade_req) {
            menteeOf.setOnClickListener(new View.OnClickListener() {

                @Override
                public void onClick(View v) {
                    startActivity(new Intent(HomeStudentPage.this, MenteeOfPage.class));
                }
            });

            possibleMenteeOf.setOnClickListener(new View.OnClickListener() {

                @Override
                public void onClick(View v) {
                    startActivity(new Intent(HomeStudentPage.this, PossibleMenteeOfPage.class));
                }
            });
        }
        else {
            menteeOf.setVisibility(View.INVISIBLE);
            possibleMenteeOf.setVisibility(View.INVISIBLE);
        }
    }
}

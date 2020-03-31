package com.example.databasephase3android;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;

public class HomeStudentPage extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_home_student);

        final Session session = new Session(MyApplication.getAppContext());

        Button logoutBtn = (Button) findViewById(R.id.logout);
        logoutBtn.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                session.logoutUser();
                startActivity(new Intent(HomeStudentPage.this, LoginPage.class));
            }
        });

        Button updateAccount = (Button) findViewById(R.id.update);
        updateAccount.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                startActivity(new Intent(HomeStudentPage.this, UpdateAccount.class));
            }
        });

        TextView welcomeUser = (TextView) findViewById (R.id.welcome);
        welcomeUser.setText("Welcome " + session.getLoggedInUserName() + "!");
    }
}

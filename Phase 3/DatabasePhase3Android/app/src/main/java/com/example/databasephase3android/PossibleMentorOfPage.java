package com.example.databasephase3android;

import android.os.Bundle;

import androidx.appcompat.app.AppCompatActivity;

public class PossibleMentorOfPage extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_possible_mentor_of);

        final Session session = new Session(MyApplication.getAppContext());
    }
}

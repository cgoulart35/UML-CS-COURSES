package com.example.databasephase3android;

import android.os.Bundle;

import androidx.appcompat.app.AppCompatActivity;

public class PossibleMenteeOfPage extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_possible_mentee_of);

        final Session session = new Session(MyApplication.getAppContext());
    }
}

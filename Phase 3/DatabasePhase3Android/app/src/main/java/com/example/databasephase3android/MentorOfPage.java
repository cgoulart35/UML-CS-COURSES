package com.example.databasephase3android;

import android.os.Bundle;

import androidx.appcompat.app.AppCompatActivity;

public class MentorOfPage extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_mentor_of);

        final Session session = new Session(MyApplication.getAppContext());
    }
}

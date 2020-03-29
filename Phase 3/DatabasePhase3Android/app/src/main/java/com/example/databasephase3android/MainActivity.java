package com.example.databasephase3android;

import androidx.appcompat.app.AppCompatActivity;

import android.content.Intent;
import android.os.Bundle;

import android.view.View;
import android.widget.Button;

public class MainActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        Button loginBtn = (Button) findViewById(R.id.login);
        loginBtn.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                startActivity(new Intent(MainActivity.this, LoginPage.class));
            }
        });

        Button regParBtn = (Button) findViewById(R.id.parentRegister);
        regParBtn.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                startActivity(new Intent(MainActivity.this, RegParPage.class));
            }
        });

        Button regStuBtn = (Button) findViewById(R.id.studentRegister);
        regStuBtn.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                startActivity(new Intent(MainActivity.this, RegStuPage.class));
            }
        });

        Button regAdmBtn = (Button) findViewById(R.id.adminRegister);
        regAdmBtn.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                startActivity(new Intent(MainActivity.this, RegAdmPage.class));
            }
        });
    }
}

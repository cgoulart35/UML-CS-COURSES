package com.example.databasephase3android;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

public class UpdateAccount extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_update);

        final Session session = new Session(MyApplication.getAppContext());

        //get current user's information and initialize the hints
        final EditText nameEditText=findViewById(R.id.name);
        nameEditText.setHint(session.getLoggedInUserName());

        final EditText gradeEditText=findViewById(R.id.grade);
        if (!session.isStudent())
            gradeEditText.setVisibility(View.INVISIBLE);
        final EditText phoneEditText=findViewById(R.id.phone);
        final EditText emailEditText=findViewById(R.id.email);
        final EditText passwordEditText=findViewById(R.id.password);

        JSONArray getUserInfoArray = UtilityClass.makePOST(String.format("SELECT phone, email, password FROM users WHERE id = '%d' LIMIT 1", session.getLoggedInUserID()));
        try {
            JSONObject getUserInfoObject = getUserInfoArray.getJSONObject(0);
            String phoneHint = getUserInfoObject.getString("phone");
            String emailHint = getUserInfoObject.getString("email");
            String passwordHint = getUserInfoObject.getString("password");

            phoneEditText.setHint(phoneHint);
            emailEditText.setHint(emailHint);
            passwordEditText.setHint(passwordHint);

        } catch (JSONException e) {
            e.printStackTrace();
        }

        if (session.isStudent()) {
            JSONArray getGradeInfoArray = UtilityClass.makePOST(String.format("SELECT grade FROM students WHERE student_id = '%d' LIMIT 1", session.getLoggedInUserID()));
            try {
                JSONObject getGradeInfoObject = getGradeInfoArray.getJSONObject(0);
                String gradeHint = getGradeInfoObject.getString("grade");
                gradeEditText.setHint(gradeHint);

            } catch (JSONException e) {
                e.printStackTrace();
            }
        }


        Button submitBtn = (Button) findViewById(R.id.submit);
        submitBtn.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {

                //read form info
                String name = nameEditText.getText().toString();

                String gradeStr = "";
                if (!session.isStudent())
                    gradeStr = gradeEditText.getText().toString();

                String phone = phoneEditText.getText().toString();

                String email = emailEditText.getText().toString();

                String password = passwordEditText.getText().toString();

                boolean requestUpdate = false;
                boolean updatedSuccessfully = true;
                if (!name.equals("")) {
                    requestUpdate = true;
                    UtilityClass.makePOST(String.format("UPDATE users SET name='%s' WHERE id='%s'", name, session.getLoggedInUserID()));
                }
                if (!gradeStr.equals("") && session.isStudent()) {
                    requestUpdate = true;
                    int grade = Integer.parseInt(gradeStr);
                    UtilityClass.makePOST(String.format("UPDATE students SET grade=%d WHERE student_id='%s'", grade, session.getLoggedInUserID()));
                }
                if (!phone.equals("")) {
                    requestUpdate = true;
                    UtilityClass.makePOST(String.format("UPDATE users SET phone='%s' WHERE id='%s'", phone, session.getLoggedInUserID()));
                }
                if (!email.equals("")) {
                    requestUpdate = true;
                    //make sure email isn't already used
                    JSONArray emailExistsArray = UtilityClass.makePOST(String.format("SELECT * FROM users WHERE email = '%s' LIMIT 1", email));
                    if (emailExistsArray != null && emailExistsArray.length() > 0) {
                        Toast errorEmailTaken = Toast.makeText(UpdateAccount.this, "Error: Email already taken.", Toast.LENGTH_SHORT);
                        errorEmailTaken.show();
                        updatedSuccessfully = false;
                    }
                    else {
                        UtilityClass.makePOST(String.format("UPDATE users SET email='%s' WHERE id='%s'", email, session.getLoggedInUserID()));
                    }
                }
                if (!password.equals("")) {
                    requestUpdate = true;
                    UtilityClass.makePOST(String.format("UPDATE users SET password='%s' WHERE id='%s'", password, session.getLoggedInUserID()));
                }
                if (!requestUpdate) {
                    Toast fillOutForm = Toast.makeText(UpdateAccount.this, "Error: Fill out form.", Toast.LENGTH_SHORT);
                    fillOutForm.show();
                }
                else if (updatedSuccessfully) {
                    goHome(session);
                }
            }
        });

        Button backBtn = (Button) findViewById(R.id.back);
        backBtn.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                goHome(session);
            }
        });
    }

    private void goHome(final Session session) {
        if (session.isAdmin()) {
            startActivity(new Intent(UpdateAccount.this, HomeAdminPage.class));
        }
        else if (session.isParent()) {
            startActivity(new Intent(UpdateAccount.this, HomeParentPage.class));
        }
        else if (session.isStudent()) {
            startActivity(new Intent(UpdateAccount.this, HomeStudentPage.class));
        }
    }
}

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

public class LoginPage extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        Button submitBtn = (Button) findViewById(R.id.submit);
        submitBtn.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {

                //read email & password on click
                EditText emailEditText=findViewById(R.id.email);
                String email = emailEditText.getText().toString();
                EditText passwordEditText=findViewById(R.id.password);
                String password = passwordEditText.getText().toString();

                //see if valid information
                JSONArray validateInfoArray = UtilityClass.makePOST(String.format("SELECT id, name, password FROM users WHERE email = '%s' LIMIT 1", email));
                if (validateInfoArray != null && validateInfoArray.length() > 0) {
                    //check to see is password is correct
                    try {
                        JSONObject validateInfoObject = validateInfoArray.getJSONObject(0);
                        String validatePassword = validateInfoObject.getString("password");
                        if(password.equals(validatePassword)) {
                            String name = validateInfoObject.getString("name");
                            int id = validateInfoObject.getInt("id");

                            //set session variables
                            Session session = new Session(MyApplication.getAppContext());
                            session.setLoggedInUser(id, name);

                            if (session.isParent()) {
                                startActivity(new Intent(LoginPage.this, HomeParentPage.class));
                            }
                            else if (session.isStudent()) {
                                startActivity(new Intent(LoginPage.this, HomeStudentPage.class));
                            }
                            else if (session.isAdmin()) {
                                startActivity(new Intent(LoginPage.this, HomeAdminPage.class));
                            }
                        }
                        else {
                            //tell user that user with that password is wrong
                            Toast errorNoUser = Toast.makeText(LoginPage.this, "Error: Incorrect password.", Toast.LENGTH_SHORT);
                            errorNoUser.show();
                        }
                    } catch (JSONException e) {
                        e.printStackTrace();
                    }
                }
                else {
                    //tell user that user with that email does not exist
                    Toast errorNoUser = Toast.makeText(LoginPage.this, "Error: No user exists.", Toast.LENGTH_SHORT);
                    errorNoUser.show();
                }
            }
        });

        Button homeBtn = (Button) findViewById(R.id.home);
        homeBtn.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                startActivity(new Intent(LoginPage.this, MainActivity.class));
            }
        });
    }
}

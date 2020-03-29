package com.example.databasephase3android;

import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

public class RegAdmPage extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_reg_adm);

        Button submitBtn = (Button) findViewById(R.id.submit);
        submitBtn.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {

                //read form info
                EditText nameEditText=findViewById(R.id.name);
                String name = nameEditText.getText().toString();

                EditText phoneEditText=findViewById(R.id.phone);
                String phone = phoneEditText.getText().toString();

                EditText emailEditText=findViewById(R.id.email);
                String email = emailEditText.getText().toString();

                EditText passwordEditText=findViewById(R.id.password);
                String password = passwordEditText.getText().toString();

                EditText confirmPasswordEditText=findViewById(R.id.confirmPassword);
                String confirmPassword = confirmPasswordEditText.getText().toString();

                if (name.isEmpty()) {
                    Toast errorNameEmpty = Toast.makeText(RegAdmPage.this, "Error: Enter a name.", Toast.LENGTH_SHORT);
                    errorNameEmpty.show();
                }
                else if (phone.isEmpty()) {
                    Toast errorPhoneEmpty = Toast.makeText(RegAdmPage.this, "Error: Enter a phone.", Toast.LENGTH_SHORT);
                    errorPhoneEmpty.show();
                }
                else if (email.isEmpty()) {
                    Toast errorEmailEmpty = Toast.makeText(RegAdmPage.this, "Error: Enter an email.", Toast.LENGTH_SHORT);
                    errorEmailEmpty.show();
                }
                else if (password.isEmpty()) {
                    Toast errorPasswordEmpty = Toast.makeText(RegAdmPage.this, "Error: Enter a password.", Toast.LENGTH_SHORT);
                    errorPasswordEmpty.show();
                }
                else if (confirmPassword.isEmpty()) {
                    Toast errorConfirmPasswordEmpty = Toast.makeText(RegAdmPage.this, "Error: Enter confirm password.", Toast.LENGTH_SHORT);
                    errorConfirmPasswordEmpty.show();
                }
                else if (!(password.equals(confirmPassword))) {
                    Toast errorPasswordMatch = Toast.makeText(RegAdmPage.this, "Error: Passwords don't match.", Toast.LENGTH_SHORT);
                    errorPasswordMatch.show();
                }
                else {
                    //make sure email isn't already used
                    JSONArray emailExistsArray = UtilityClass.makePOST(String.format("SELECT * FROM users WHERE email = '%s' LIMIT 1", email));

                    if (emailExistsArray != null && emailExistsArray.length() > 0) {
                        Toast errorEmailTaken = Toast.makeText(RegAdmPage.this, "Error: Email already taken.", Toast.LENGTH_SHORT);
                        errorEmailTaken.show();
                    }
                    else {
                        //insert into users
                        UtilityClass.makePOST(String.format("INSERT INTO users (email, password, name, phone) VALUES('%s', '%s', '%s', '%s')", email, password, name, phone));

                        try {
                            //get id of admin
                            JSONArray admin_id_array = UtilityClass.makePOST(String.format("SELECT id FROM users WHERE email = '%s'", email));
                            JSONObject admin_id_object = admin_id_array.getJSONObject(0);
                            int admin_id = admin_id_object.getInt("id");

                            //insert into admins
                            UtilityClass.makePOST(String.format("INSERT INTO admins (admin_id) VALUES(%d)", admin_id));
                        } catch (JSONException e) {
                            e.printStackTrace();
                        }
                    }
                }
            }
        });
    }
}

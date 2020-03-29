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

public class RegStuPage extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_reg_stu);

        Button submitBtn = (Button) findViewById(R.id.submit);
        submitBtn.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {

                //read form info
                EditText nameEditText=findViewById(R.id.name);
                String name = nameEditText.getText().toString();

                EditText gradeEditText=findViewById(R.id.grade);
                String gradeStr = gradeEditText.getText().toString();

                EditText phoneEditText=findViewById(R.id.phone);
                String phone = phoneEditText.getText().toString();

                EditText emailEditText=findViewById(R.id.email);
                String email = emailEditText.getText().toString();

                EditText parentEmailEditText=findViewById(R.id.parentEmail);
                String parentEmail = parentEmailEditText.getText().toString();

                EditText passwordEditText=findViewById(R.id.password);
                String password = passwordEditText.getText().toString();

                EditText confirmPasswordEditText=findViewById(R.id.confirmPassword);
                String confirmPassword = confirmPasswordEditText.getText().toString();

                if (name.isEmpty()) {
                    Toast errorNameEmpty = Toast.makeText(RegStuPage.this, "Error: Enter a name.", Toast.LENGTH_SHORT);
                    errorNameEmpty.show();
                }
                else if (gradeStr.isEmpty()) {
                    Toast errorGradeEmpty = Toast.makeText(RegStuPage.this, "Error: Enter a grade.", Toast.LENGTH_SHORT);
                    errorGradeEmpty.show();
                }
                else if (phone.isEmpty()) {
                    Toast errorPhoneEmpty = Toast.makeText(RegStuPage.this, "Error: Enter a phone.", Toast.LENGTH_SHORT);
                    errorPhoneEmpty.show();
                }
                else if (email.isEmpty()) {
                    Toast errorEmailEmpty = Toast.makeText(RegStuPage.this, "Error: Enter an email.", Toast.LENGTH_SHORT);
                    errorEmailEmpty.show();
                }
                else if (parentEmail.isEmpty()) {
                    Toast errorParentEmailEmpty = Toast.makeText(RegStuPage.this, "Error: Enter a parent email.", Toast.LENGTH_SHORT);
                    errorParentEmailEmpty.show();
                }
                else if (password.isEmpty()) {
                    Toast errorPasswordEmpty = Toast.makeText(RegStuPage.this, "Error: Enter a password.", Toast.LENGTH_SHORT);
                    errorPasswordEmpty.show();
                }
                else if (confirmPassword.isEmpty()) {
                    Toast errorConfirmPasswordEmpty = Toast.makeText(RegStuPage.this, "Error: Enter confirm password.", Toast.LENGTH_SHORT);
                    errorConfirmPasswordEmpty.show();
                }
                else if (!(password.equals(confirmPassword))) {
                    Toast errorPasswordMatch = Toast.makeText(RegStuPage.this, "Error: Passwords don't match.", Toast.LENGTH_SHORT);
                    errorPasswordMatch.show();
                }
                else {
                    int grade = Integer.parseInt(gradeStr);

                    //make sure email isn't already used
                    JSONArray emailExistsArray = UtilityClass.makePOST(String.format("SELECT * FROM users WHERE email = '%s' LIMIT 1", email));
                    //make sure parent with email exists
                    JSONArray parentExistsArray = UtilityClass.makePOST(String.format("SELECT * FROM users INNER JOIN parents ON users.id = parents.parent_id WHERE email = '%s' LIMIT 1", parentEmail));

                    if (emailExistsArray != null && emailExistsArray.length() > 0) {
                        Toast errorEmailTaken = Toast.makeText(RegStuPage.this, "Error: Email already taken.", Toast.LENGTH_SHORT);
                        errorEmailTaken.show();
                    }
                    else if (parentExistsArray == null || parentExistsArray.length() == 0) {
                        Toast errorParentEmail = Toast.makeText(RegStuPage.this, "Error: Parent email does not exist.", Toast.LENGTH_SHORT);
                        errorParentEmail.show();
                    }
                    else {
                        //insert into users
                        UtilityClass.makePOST(String.format("INSERT INTO users (email, password, name, phone) VALUES('%s', '%s', '%s', '%s')", email, password, name, phone));

                        try {
                            //get id of student
                            JSONArray student_id_array = UtilityClass.makePOST(String.format("SELECT id FROM users WHERE email = '%s'", email));
                            JSONObject student_id_object = student_id_array.getJSONObject(0);
                            int student_id = student_id_object.getInt("id");

                            //get id of parent
                            JSONArray parent_id_array = UtilityClass.makePOST(String.format("SELECT id FROM users WHERE email = '%s'", parentEmail));
                            JSONObject parent_id_object = parent_id_array.getJSONObject(0);
                            int parent_id = parent_id_object.getInt("id");

                            //insert into students
                            UtilityClass.makePOST(String.format("INSERT INTO students (student_id, grade, parent_id) VALUES(%d, %d, %d)", student_id, grade, parent_id));
                        } catch (JSONException e) {
                            e.printStackTrace();
                        }
                    }
                }
            }
        });
    }
}

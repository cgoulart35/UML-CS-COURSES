package com.example.databasephase3android;

import android.content.Intent;
import android.graphics.Typeface;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.TableLayout;
import android.widget.TableRow;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

public class HomeParentPage extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_home_parent);

        final Session session = new Session(MyApplication.getAppContext());

        TextView welcomeUser = findViewById (R.id.welcome);
        welcomeUser.setText("Welcome " + session.getLoggedInUserName() + "!");

        TextView usersPage = findViewById (R.id.usersPage);
        usersPage.setText(session.getUserToEditName() + "'s Parent Page:");

        Button homeBtn = findViewById(R.id.home);
        if (session.isAdmin()) {
            homeBtn.setOnClickListener(new View.OnClickListener() {

                @Override
                public void onClick(View v) {
                    session.setUserToEdit(session.getLoggedInUserID(), session.getLoggedInUserName());
                    startActivity(new Intent(HomeParentPage.this, HomeAdminPage.class));
                }
            });
        }
        else {
            homeBtn.setVisibility(View.INVISIBLE);
        }

        Button logoutBtn = findViewById(R.id.logout);
        logoutBtn.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                session.logoutUser();
                startActivity(new Intent(HomeParentPage.this, LoginPage.class));
            }
        });

        Button updateAccount = findViewById(R.id.update);
        updateAccount.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                startActivity(new Intent(HomeParentPage.this, UpdateAccount.class));
            }
        });

        // show all children of the parent on a table
        TableLayout childrenTable = findViewById(R.id.childrenTable);

        TableRow tableName = new TableRow(this);
        TableRow.LayoutParams lp = new TableRow.LayoutParams(TableRow.LayoutParams.MATCH_PARENT);
        tableName.setLayoutParams(lp);

        TextView tableNameCol = new TextView(this);
        tableNameCol.setText("CHILDREN TABLE:");
        tableNameCol.setTypeface(null, Typeface.BOLD);

        tableName.addView(tableNameCol, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        childrenTable.addView(tableName, 0);

        TableRow headers = new TableRow(this);
        headers.setLayoutParams(lp);

        TextView id_col  = new TextView(this);
        id_col.setText("ID:");
        id_col.setTypeface(null, Typeface.BOLD);

        TextView name_col = new TextView(this);
        name_col.setText("NAME:");
        name_col.setTypeface(null, Typeface.BOLD);

        TextView grade_col = new TextView(this);
        grade_col.setText("GRADE:");
        grade_col.setTypeface(null, Typeface.BOLD);

        TextView edit_col  = new TextView(this);
        edit_col.setText("EDIT STUDENT:");
        edit_col.setTypeface(null, Typeface.BOLD);

        headers.addView(id_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        headers.addView(name_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        headers.addView(grade_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        headers.addView(edit_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        childrenTable.addView(headers, 1);

        // get children of parent query
        JSONArray getChildrenArray = UtilityClass.makePOST(String.format("SELECT id, name FROM users INNER JOIN students ON users.id = students.student_id WHERE parent_id = %d", session.getUserToEditID()));

        for (int i = 2; i < getChildrenArray.length() + 2; i++) {
            String name = "";
            int id = 0;
            String grade = "N/A";
            try {
                JSONObject getChildObject = getChildrenArray.getJSONObject(i - 2);
                name = getChildObject.getString("name");
                id = getChildObject.getInt("id");
                if (UtilityClass.isUserAStudent(id)) {
                    JSONArray getGradeArray = UtilityClass.makePOST(String.format("SELECT grade FROM students WHERE student_id = %d", id));
                    JSONObject getGradeObject = getGradeArray.getJSONObject(0);
                    grade = Integer.toString(getGradeObject.getInt("grade"));
                }
            } catch (JSONException e) {
                e.printStackTrace();
            }

            TableRow row = new TableRow(this);
            lp = new TableRow.LayoutParams(TableRow.LayoutParams.MATCH_PARENT);
            row.setLayoutParams(lp);

            TextView idView = new TextView(this);
            idView.setText(Integer.toString(id));

            TextView nameView = new TextView(this);
            nameView.setText(name);

            TextView gradeView = new TextView(this);
            gradeView.setText(grade);

            Button edit = new Button(this);
            edit.setText("Edit Student");
            edit.setTag(R.string.id_tag, id);
            edit.setTag(R.string.name_tag, name);
            edit.setOnClickListener(new View.OnClickListener() {

                @Override
                public void onClick(View v) {
                    session.setUserToEdit((int)v.getTag(R.string.id_tag), v.getTag(R.string.name_tag).toString());
                    startActivity(new Intent(HomeParentPage.this, HomeStudentPage.class));
                }
            });

            row.addView(idView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            row.addView(nameView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            row.addView(gradeView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            row.addView(edit, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            childrenTable.addView(row,i);
        }
    }
}

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

public class HomeAdminPage extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_home_admin);

        final Session session = new Session(MyApplication.getAppContext());

        TextView welcomeUser = findViewById (R.id.welcome);
        welcomeUser.setText("Welcome " + session.getLoggedInUserName() + "!");

        TextView usersPage = findViewById (R.id.usersPage);
        usersPage.setText(session.getUserToEditName() + "'s Admin Page:");

        Button logoutBtn = findViewById(R.id.logout);
        logoutBtn.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                session.logoutUser();
                startActivity(new Intent(HomeAdminPage.this, LoginPage.class));
            }
        });

        Button updateAccount = findViewById(R.id.update);
        updateAccount.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                startActivity(new Intent(HomeAdminPage.this, UpdateAccount.class));
            }
        });

        // show all users on a table
        TableLayout usersTable = findViewById(R.id.usersTable);

        TableRow tableName = new TableRow(this);
        TableRow.LayoutParams lp = new TableRow.LayoutParams(TableRow.LayoutParams.MATCH_PARENT);
        tableName.setLayoutParams(lp);

        TextView tableNameCol = new TextView(this);
        tableNameCol.setText("USERS TABLE:");
        tableNameCol.setTypeface(null, Typeface.BOLD);

        tableName.addView(tableNameCol, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        usersTable.addView(tableName, 0);

        TableRow headers = new TableRow(this);
        headers.setLayoutParams(lp);

        TextView id_col  = new TextView(this);
        id_col.setText("ID:");
        id_col.setTypeface(null, Typeface.BOLD);

        TextView name_col = new TextView(this);
        name_col.setText("NAME:");
        name_col.setTypeface(null, Typeface.BOLD);

        TextView edit_col  = new TextView(this);
        edit_col.setText("EDIT:");
        edit_col.setTypeface(null, Typeface.BOLD);

        headers.addView(id_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        headers.addView(name_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        headers.addView(edit_col, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
        usersTable.addView(headers, 1);

        // get all users query
        JSONArray getUsersArray = UtilityClass.makePOST(String.format("SELECT id, name FROM users"));

        for (int i = 2; i < getUsersArray.length() + 2; i++) {
            String name = "";
            int id = 0;
            try {
                JSONObject getChildObject = getUsersArray.getJSONObject(i - 2);
                name = getChildObject.getString("name");
                id = getChildObject.getInt("id");
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

            Button edit = new Button(this);
            edit.setText("Edit");
            edit.setTag(R.string.id_tag, id);
            edit.setTag(R.string.name_tag, name);
            edit.setOnClickListener(new View.OnClickListener() {

                @Override
                public void onClick(View v) {
                    session.setUserToEdit((int)v.getTag(R.string.id_tag), v.getTag(R.string.name_tag).toString());
                    if (session.isUserToEditStudent()) {
                        startActivity(new Intent(HomeAdminPage.this, HomeStudentPage.class));
                    }
                    else if (session.isUserToEditParent()) {
                        startActivity(new Intent(HomeAdminPage.this, HomeParentPage.class));
                    }
                    else if (session.isUserToEditAdmin()) {
                        startActivity(new Intent(HomeAdminPage.this, HomeAdminPage.class));
                    }
                }
            });

            row.addView(idView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            row.addView(nameView, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            row.addView(edit, new TableRow.LayoutParams(0, TableRow.LayoutParams.WRAP_CONTENT, 1f));
            usersTable.addView(row,i);
        }
    }
}

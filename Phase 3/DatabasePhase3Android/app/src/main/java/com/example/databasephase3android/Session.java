package com.example.databasephase3android;

import android.content.Context;
import android.content.SharedPreferences;
import android.preference.PreferenceManager;

import org.json.JSONArray;

public class Session {

    private SharedPreferences prefs;

    public Session(Context context) {
        prefs = PreferenceManager.getDefaultSharedPreferences(context);
    }

    public void setLoggedInUser(int userID, String name) {
        prefs.edit().putInt("userID", userID).commit();
        prefs.edit().putString("name", name).commit();

        boolean isParent = false;
        boolean isStudent = false;
        boolean isAdmin = false;

        //figure out with queries if student, parent, or admin
        JSONArray isParentQueryResult = UtilityClass.makePOST(String.format("SELECT * FROM parents WHERE parent_id = %d", userID));
        JSONArray isStudentQueryResult = UtilityClass.makePOST(String.format("SELECT * FROM students WHERE student_id = %d", userID));
        JSONArray isAdminQueryResult = UtilityClass.makePOST(String.format("SELECT * FROM admins WHERE admin_id = %d", userID));

        if (isParentQueryResult != null && isParentQueryResult.length() > 0) {
            isParent = true;
        }
        else if (isStudentQueryResult != null && isStudentQueryResult.length() > 0) {
            isStudent = true;
        }
        else if (isAdminQueryResult != null && isAdminQueryResult.length() > 0) {
            isAdmin = true;
        }

        prefs.edit().putBoolean("isParent", isParent).commit();
        prefs.edit().putBoolean("isStudent", isStudent).commit();
        prefs.edit().putBoolean("isAdmin", isAdmin).commit();
    }

    public int getLoggedInUserID() {
        int userID = prefs.getInt("userID", 0);
        return userID;
    }

    public String getLoggedInUserName() {
        String name = prefs.getString("name", "");
        return name;
    }

    public boolean isParent() {
        boolean isParent = prefs.getBoolean("isParent", false);
        return isParent;
    }

    public boolean isStudent() {
        boolean isStudent = prefs.getBoolean("isStudent", false);
        return isStudent;
    }

    public boolean isAdmin() {
        boolean isAdmin = prefs.getBoolean("isAdmin", false);
        return isAdmin;
    }

    public void logoutUser() {
        prefs.edit().clear();

        /*prefs.edit().putInt("userID", .....).commit();
        prefs.edit().putString("name", "").commit();
        prefs.edit().putBoolean("isParent", false).commit();
        prefs.edit().putBoolean("isStudent", false).commit();
        prefs.edit().putBoolean("isAdmin", false).commit();*/
    }
}
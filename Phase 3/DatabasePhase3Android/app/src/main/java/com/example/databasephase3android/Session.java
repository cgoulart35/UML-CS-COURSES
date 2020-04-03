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

    public void setUserToEdit(int userToEditID, String userToEditName) {
        prefs.edit().putInt("userToEditID", userToEditID).commit();
        prefs.edit().putString("userToEditName", userToEditName).commit();

        boolean isUserToEditParent = false;
        boolean isUserToEditStudent = false;
        boolean isUserToEditAdmin = false;

        //figure out with queries if student, parent, or admin
        JSONArray isParentQueryResult = UtilityClass.makePOST(String.format("SELECT * FROM parents WHERE parent_id = %d", userToEditID));
        JSONArray isStudentQueryResult = UtilityClass.makePOST(String.format("SELECT * FROM students WHERE student_id = %d", userToEditID));
        JSONArray isAdminQueryResult = UtilityClass.makePOST(String.format("SELECT * FROM admins WHERE admin_id = %d", userToEditID));

        if (isParentQueryResult != null && isParentQueryResult.length() > 0) {
            isUserToEditParent = true;
        }
        else if (isStudentQueryResult != null && isStudentQueryResult.length() > 0) {
            isUserToEditStudent = true;
        }
        else if (isAdminQueryResult != null && isAdminQueryResult.length() > 0) {
            isUserToEditAdmin = true;
        }

        prefs.edit().putBoolean("isUserToEditParent", isUserToEditParent).commit();
        prefs.edit().putBoolean("isUserToEditStudent", isUserToEditStudent).commit();
        prefs.edit().putBoolean("isUserToEditAdmin", isUserToEditAdmin).commit();
    }

    public int getUserToEditID() {
        int userToEditID = prefs.getInt("userToEditID", 0);
        return userToEditID;
    }

    public String getUserToEditName() {
        String userToEditName = prefs.getString("userToEditName", "");
        return userToEditName;
    }

    public boolean isUserToEditParent() {
        boolean isUserToEditParent = prefs.getBoolean("isUserToEditParent", false);
        return isUserToEditParent;
    }

    public boolean isUserToEditStudent() {
        boolean isUserToEditStudent = prefs.getBoolean("isUserToEditStudent", false);
        return isUserToEditStudent;
    }

    public boolean isUserToEditAdmin() {
        boolean isUserToEditAdmin = prefs.getBoolean("isUserToEditAdmin", false);
        return isUserToEditAdmin;
    }

    public void logoutUser() {
        prefs.edit().clear();
    }
}
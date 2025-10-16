@assignsubmission_externalserver @assignsubmission
Feature: In order to test the external server submission plugin
  I will add the external server shipped with the plugin
  and test it.

  Scenario: Check external server demopackage
    Given I add an external server pointing to this Moodle site
    And I log in as "admin"
    And I am on homepage
    And I follow "Site administration"
    And I follow "Plugins"
    And I follow "Submission to external server"
    When I follow "Check connection"
    Then "//*[@data-behat='success-1']" "xpath_element" should exist
    Then "//*[@data-behat='success-2']" "xpath_element" should exist
    Then "//*[@data-behat='success-4']" "xpath_element" should exist
    Then "//*[@data-behat='success-5']" "xpath_element" should exist

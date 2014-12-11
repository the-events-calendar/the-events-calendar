package testingautomation;

import java.util.concurrent.TimeUnit;

import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebDriverBackedSelenium;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.annotations.AfterClass;
import org.testng.annotations.BeforeClass;

import com.thoughtworks.selenium.SeleneseTestBase;
import com.thoughtworks.selenium.Selenium;


public class SetUpBase extends SeleneseTestBase {
 

	static WebDriver driver;

	static Selenium selenium;
	

	
	
	

  @BeforeClass
  public void beforeClass() throws InterruptedException {
 
  
	  System.setProperty("webdriver.chrome.driver","//Applications//chrome//chromedriver");
	  
	  driver= new ChromeDriver();
	  		 driver.manage().timeouts().implicitlyWait(40, TimeUnit.SECONDS);
	  		 
	  		 
	  		 
	  		 String baseUrl = " http://plugins.tri.be/wp-login.php ";
	  		 
	            
	  		 		

	  			driver.get(baseUrl); 
	  			
	  			selenium = new WebDriverBackedSelenium(driver,baseUrl);




		
		
		}
  
	public void waitForDuration (int timeInSeconds){
		
	    try {
			Thread.sleep(timeInSeconds * 1000);
		} catch (InterruptedException e) {
			e.printStackTrace();
		}
	}
	
	public void waitForExpectedPage(int timeInSeconds, String pageTitle){
		
		WebDriverWait wait = new WebDriverWait(driver, timeInSeconds);		
		wait.until(ExpectedConditions.titleContains(pageTitle));
		
	}	
	  


  @AfterClass
  public void afterClass() {
  
	  driver.quit();
  
  
	  
  
  }

  

}
